<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Http\Requests\Booking\PickSlotRequest;
use App\Http\Requests\Booking\SlotIntentRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Direction;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Specialization;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingFlowController extends Controller
{
    public const SESSION_PENDING = 'booking_pending';

    public const SESSION_SLOT_DRAFT = 'booking_slot_draft';

    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function start(Request $request): View|RedirectResponse
    {
        $from = $request->query('from');

        if (! is_string($from) || $from === '') {
            $request->session()->forget([self::SESSION_PENDING, self::SESSION_SLOT_DRAFT]);

            return view('booking.start');
        }

        $this->mergePendingFromQuery($request, ['from' => $from]);

        $r = $this->redirectIfFromPrefilled();
        if ($r !== null) {
            return $r;
        }

        return view('booking.start');
    }

    public function browseBookingDoctors(): View
    {
        $doctors = Doctor::query()
            ->active()
            ->whereHas('schedules')
            ->with('specialization')
            ->orderBy('sort_order')
            ->get();

        $specializations = Specialization::orderBy('name')->get();

        return view('booking.browse-doctors', [
            'doctors' => $doctors,
            'specializations' => $specializations,
        ]);
    }

    public function pickService(Request $request): View|RedirectResponse
    {
        $this->mergePendingFromQuery($request, [
            'from' => $request->query('from'),
        ]);

        $fromRaw = $request->query('from');
        $pending = session(self::SESSION_PENDING, []);
        $from = is_string($fromRaw) && $fromRaw !== ''
            ? $fromRaw
            : ($pending['from'] ?? 'any');

        $pinnedDoctor = null;
        if (is_string($from) && str_starts_with($from, 'doctor:')) {
            $slug = trim(substr($from, strlen('doctor:')));
            if ($slug !== '') {
                $pinnedDoctor = Doctor::findBySlug($slug);
            }
        }

        if (is_string($from) && str_starts_with($from, 'doctor:') && $pinnedDoctor === null) {
            return redirect()->route('booking.start')
                ->with('error', 'Врач не найден или недоступен для записи.');
        }

        $servicesQuery = Service::query()->active()->with('direction')->orderBy('name');
        if ($pinnedDoctor !== null) {
            $servicesQuery->whereHas('doctors', fn ($q) => $q->where('doctors.id', $pinnedDoctor->id));
        }

        $services = $servicesQuery->get();

        $directionsOrdered = Direction::query()->active()->orderBy('sort_order')->get();
        $directionBlocks = $directionsOrdered
            ->map(function (Direction $direction) use ($services) {
                $subset = $services->filter(fn (Service $s) => $s->direction_id === $direction->id)->values();

                return [
                    'direction' => $direction,
                    'services' => $subset,
                ];
            })
            ->filter(fn (array $block) => $block['services']->isNotEmpty())
            ->values();

        $servicesWithoutDirection = $services->filter(fn (Service $s) => $s->direction_id === null)->values();

        return view('booking.service', [
            'directionBlocks' => $directionBlocks,
            'servicesWithoutDirection' => $servicesWithoutDirection,
            'pinnedDoctor' => $pinnedDoctor,
            'from' => $from,
        ]);
    }

    public function pickDoctor(Request $request): View|RedirectResponse
    {
        $this->mergePendingFromQuery($request, [
            'service_slug' => $request->query('service'),
        ]);

        $serviceSlug = $request->query('service')
            ?? data_get(session(self::SESSION_PENDING), 'service_slug');
        if (! is_string($serviceSlug) || $serviceSlug === '') {
            return redirect()->route('booking.pickService', ['from' => 'any'])
                ->with('error', 'Сначала выберите услугу.');
        }

        $service = Service::query()->active()->where('slug', $serviceSlug)->first();
        if ($service === null) {
            return redirect()->route('booking.pickService', ['from' => 'any'])
                ->with('error', 'Услуга не найдена.');
        }

        $pinnedDoctor = null;
        $from = data_get(session(self::SESSION_PENDING), 'from');
        if (is_string($from) && str_starts_with($from, 'doctor:')) {
            $slug = trim(substr($from, strlen('doctor:')));
            if ($slug !== '') {
                $pinnedDoctor = Doctor::findBySlug($slug);
            }
        }

        $doctorsQuery = $service->doctors()
            ->active()
            ->whereHas('schedules')
            ->with('specialization')
            ->orderBy('sort_order');

        if ($pinnedDoctor !== null) {
            $doctorsQuery->where('doctors.id', $pinnedDoctor->id);
        }

        $doctors = $doctorsQuery->get();

        // Врач уже выбран ранее и оказывает эту услугу — пропустить шаг выбора врача
        if ($pinnedDoctor !== null && $doctors->isNotEmpty()) {
            return redirect()->route('booking.pickSlot', [
                'service' => $service->slug,
                'doctor'  => $pinnedDoctor->slug,
            ]);
        }

        return view('booking.doctor', [
            'service' => $service,
            'doctors' => $doctors,
            'pinnedDoctor' => $pinnedDoctor,
        ]);
    }

    public function pickSlot(PickSlotRequest $request): View|RedirectResponse
    {
        $this->mergePendingFromQuery($request, array_filter([
            'service_slug' => $request->query('service'),
            'doctor_slug' => $request->query('doctor'),
            'date' => $request->query('date'),
        ], fn ($v) => filled($v)));

        $validated = $request->validated();

        $service = Service::query()->active()->where('slug', $validated['service'])->first();
        $doctor = Doctor::findBySlug((string) $validated['doctor']);

        if ($service === null || $doctor === null) {
            return redirect()->route('booking.start')
                ->with('error', 'Некорректные данные записи.');
        }

        if (! $doctor->services()->where('services.id', $service->id)->exists()) {
            return redirect()->route('booking.pickDoctor', ['service' => $service->slug])
                ->with('error', 'Этот врач не оказывает выбранную услугу.');
        }

        if (! $doctor->schedules()->exists()) {
            return redirect()->route('booking.pickDoctor', ['service' => $service->slug])
                ->with('error', 'У врача не настроено расписание.');
        }

        $dates = $this->bookingService->availableDates(
            $doctor,
            $service,
            CarbonInterval::days(30),
        );

        $selectedDateStr = isset($validated['date']) && is_string($validated['date'])
            ? $validated['date']
            : null;
        if ($selectedDateStr === null || ! in_array($selectedDateStr, $dates, true)) {
            $selectedDateStr = $dates[0] ?? null;
        }

        $slots = collect();
        if ($selectedDateStr !== null) {
            $day = CarbonImmutable::parse($selectedDateStr, config('app.timezone'))->startOfDay();
            $slots = $this->bookingService->availableSlots($doctor, $service, $day);
        }

        $draft = [];
        $hasOldInput = $request->session()->get('_old_input') !== null;
        if (! $hasOldInput) {
            $pulled = $request->session()->pull(self::SESSION_SLOT_DRAFT, []);
            $draft = is_array($pulled) ? $pulled : [];
        }

        $prefillStartAt = old('start_at', $draft['start_at'] ?? null);
        $prefillNote = old('note', $draft['note'] ?? null);

        $singleDoctor = false;

        return view('booking.slot', [
            'service' => $service,
            'doctor' => $doctor,
            'availableDates' => $dates,
            'selectedDate' => $selectedDateStr,
            'slots' => $slots,
            'prefillStartAt' => is_string($prefillStartAt) ? $prefillStartAt : null,
            'prefillNote' => is_string($prefillNote) ? $prefillNote : null,
            'singleDoctor' => $singleDoctor,
        ]);
    }

    public function rememberSlotIntent(SlotIntentRequest $request): RedirectResponse
    {
        if (Auth::guard('patient')->check()) {
            return redirect()->route('booking.pickSlot', [
                'service' => $request->validated('service'),
                'doctor' => $request->validated('doctor'),
            ])->with('error', 'Вы уже вошли — подтвердите запись на странице.');
        }

        $validated = $request->validated();
        $service = Service::query()->active()->where('slug', $validated['service'])->first();
        $doctor = Doctor::findBySlug((string) $validated['doctor']);
        if ($service === null || $doctor === null) {
            return redirect()->route('booking.start')->with('error', 'Некорректные данные записи.');
        }

        session([
            self::SESSION_SLOT_DRAFT => [
                'start_at' => (string) $validated['start_at'],
                'note' => isset($validated['note']) ? (string) $validated['note'] : '',
            ],
        ]);

        session([
            'url.intended' => route('booking.pickSlot', [
                'service' => $service->slug,
                'doctor' => $doctor->slug,
                'date' => CarbonImmutable::parse((string) $validated['start_at'], config('app.timezone'))->format('Y-m-d'),
            ]),
        ]);

        return redirect()->route('patient.login')
            ->with('status', 'Войдите по телефону, чтобы завершить запись.');
    }

    public function confirm(StoreBookingRequest $request): RedirectResponse
    {
        $patient = $request->user('patient');
        if ($patient === null) {
            return redirect()->route('patient.login');
        }

        $validated = $request->validated();
        $service = Service::query()->active()->whereKey((int) $validated['service_id'])->firstOrFail();
        $doctor = Doctor::query()->active()->whereKey((int) $validated['doctor_id'])->firstOrFail();

        $startsAt = CarbonImmutable::parse((string) $validated['start_at'], config('app.timezone'));

        $note = isset($validated['note']) ? trim((string) $validated['note']) : '';
        $note = $note !== '' ? $note : null;

        try {
            $appointment = $this->bookingService->book($patient, $doctor, $service, $startsAt, $note);
        } catch (BookingException $e) {
            return back()->withInput()->withErrors(['start_at' => $e->getMessage()]);
        }

        $request->session()->forget([self::SESSION_PENDING, self::SESSION_SLOT_DRAFT]);

        return redirect()->route('cabinet.appointments.show', $appointment)
            ->with('status', 'Запись создана. Ждём вас в назначенное время.');
    }

    private function redirectIfFromPrefilled(): ?RedirectResponse
    {
        $pending = session(self::SESSION_PENDING, []);
        $from = $pending['from'] ?? null;

        if (is_string($from) && str_starts_with($from, 'doctor:')) {
            $slug = trim(substr($from, strlen('doctor:')));
            if ($slug !== '' && Doctor::findBySlug($slug) !== null) {
                return redirect()->route('booking.pickService', ['from' => 'doctor:'.$slug]);
            }
        }

        if (is_string($from) && str_starts_with($from, 'service:')) {
            $slug = trim(substr($from, strlen('service:')));
            if ($slug !== '') {
                $service = Service::query()->active()->where('slug', $slug)->first();
                if ($service !== null) {
                    return redirect()->route('booking.pickDoctor', ['service' => $service->slug]);
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function mergePendingFromQuery(Request $request, array $data): void
    {
        $clean = array_filter(
            $data,
            static fn ($v) => $v !== null && (! is_string($v) || $v !== ''),
        );

        if ($clean === []) {
            return;
        }

        session([
            self::SESSION_PENDING => array_merge(session(self::SESSION_PENDING, []), $clean),
        ]);
    }
}
