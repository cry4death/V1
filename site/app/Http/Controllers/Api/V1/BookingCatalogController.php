<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DoctorListResource;
use App\Http\Resources\Api\ServiceListResource;
use App\Models\Doctor;
use App\Models\Service;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class BookingCatalogController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function services(Request $request): AnonymousResourceCollection
    {
        $query = Service::query()->active()->with('direction')->orderBy('name');

        $doctorSlug = $request->query('doctor');
        if (is_string($doctorSlug) && $doctorSlug !== '') {
            $doctor = Doctor::findBySlug($doctorSlug);
            if ($doctor === null) {
                return ServiceListResource::collection(collect());
            }
            $query->whereHas('doctors', fn ($q) => $q->where('doctors.id', $doctor->id));
        }

        return ServiceListResource::collection($query->get());
    }

    public function doctors(Request $request): AnonymousResourceCollection
    {
        $slug = $request->query('service');
        if (! is_string($slug) || $slug === '') {
            throw ValidationException::withMessages([
                'service' => ['Укажите параметр service (slug услуги).'],
            ]);
        }

        $service = Service::query()->active()->where('slug', $slug)->first();
        if ($service === null) {
            return DoctorListResource::collection(collect());
        }

        $doctors = $service->doctors()
            ->active()
            ->whereHas('schedules')
            ->with('specialization')
            ->orderBy('sort_order')
            ->get();

        return DoctorListResource::collection($doctors);
    }

    public function slots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service' => ['required', 'string', 'max:255'],
            'doctor' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $service = Service::query()->active()->where('slug', $validated['service'])->first();
        $doctor = Doctor::findBySlug($validated['doctor']);
        if ($service === null || $doctor === null) {
            return response()->json(['data' => []]);
        }

        $day = CarbonImmutable::parse($validated['date'], config('app.timezone'))->startOfDay();
        $slots = $this->bookingService->availableSlots($doctor, $service, $day);

        /** Формат без Z/+ для POST формы: + в urlencoded часто ломает разбор и даёт сдвиг на часы. */
        $tz = config('app.timezone');

        return response()->json([
            'data' => $slots->map(fn (CarbonImmutable $t) => $t->timezone($tz)->format('Y-m-d\TH:i:s'))->values()->all(),
        ]);
    }

    public function dates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service' => ['required', 'string', 'max:255'],
            'doctor' => ['required', 'string', 'max:255'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $service = Service::query()->active()->where('slug', $validated['service'])->first();
        $doctor = Doctor::findBySlug($validated['doctor']);
        if ($service === null || $doctor === null) {
            return response()->json(['data' => []]);
        }

        $tz = config('app.timezone');
        $today = CarbonImmutable::now($tz)->startOfDay();
        $from = isset($validated['from'])
            ? CarbonImmutable::parse($validated['from'], $tz)->startOfDay()
            : $today;
        $to = isset($validated['to'])
            ? CarbonImmutable::parse($validated['to'], $tz)->startOfDay()
            : $today->addDays(60);

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        if ($from->diffInDays($to) > 120) {
            throw ValidationException::withMessages([
                'to' => ['Интервал не больше 120 дней.'],
            ]);
        }

        $dates = $this->bookingService->availableDatesBetween($doctor, $service, $from, $to);

        return response()->json(['data' => $dates]);
    }
}
