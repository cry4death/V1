<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\AppointmentStatus;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cabinet\CancelAppointmentRequest;
use App\Http\Requests\Cabinet\RescheduleAppointmentRequest;
use App\Http\Requests\Cabinet\UpdateCabinetProfileRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Setting;
use App\Services\BookingService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CabinetController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function dashboard(Request $request): View
    {
        /** @var Patient $patient */
        $patient = Auth::guard('patient')->user();

        $tab = $request->query('tab', 'upcoming');
        if (! in_array($tab, ['upcoming', 'past'], true)) {
            $tab = 'upcoming';
        }

        $query = $patient->appointments()->with(['doctor.specialization', 'service.direction']);

        if ($tab === 'upcoming') {
            $query
                ->where('start_at', '>=', now())
                ->whereNotIn('status', [AppointmentStatus::Cancelled, AppointmentStatus::Rescheduled])
                ->orderBy('start_at');
        } else {
            $query
                ->where(function ($q): void {
                    $q->where('start_at', '<', now())
                        ->orWhereIn('status', [
                            AppointmentStatus::Completed,
                            AppointmentStatus::Cancelled,
                            AppointmentStatus::Rescheduled,
                        ]);
                })
                ->orderByDesc('start_at');
        }

        $appointments = $query->simplePaginate(10)->withQueryString();

        return view('cabinet.dashboard', [
            'patient' => $patient,
            'tab' => $tab,
            'appointments' => $appointments,
        ]);
    }

    public function appointments(Request $request): View
    {
        /** @var Patient $patient */
        $patient = Auth::guard('patient')->user();

        $tab = $request->query('tab', 'upcoming');
        if (! in_array($tab, ['upcoming', 'past'], true)) {
            $tab = 'upcoming';
        }

        $query = $patient->appointments()->with(['doctor.specialization', 'service.direction']);

        if ($tab === 'upcoming') {
            $query
                ->where('start_at', '>=', now())
                ->whereNotIn('status', [
                    AppointmentStatus::Cancelled,
                    AppointmentStatus::Rescheduled,
                ])
                ->orderBy('start_at');
        } else {
            $query
                ->where(function ($q): void {
                    $q->where('start_at', '<', now())
                        ->orWhereIn('status', [
                            AppointmentStatus::Completed,
                            AppointmentStatus::Cancelled,
                            AppointmentStatus::Rescheduled,
                        ]);
                })
                ->orderByDesc('start_at');
        }

        $appointments = $query->simplePaginate(10)->withQueryString();

        return view('cabinet.appointments', [
            'tab' => $tab,
            'appointments' => $appointments,
        ]);
    }

    public function show(Appointment $appointment): View
    {
        /** @var Patient $patient */
        $patient = Auth::guard('patient')->user();
        $this->assertOwnsAppointment($patient, $appointment);

        $appointment->load(['doctor.specialization', 'service.direction']);

        return view('cabinet.show', [
            'appointment' => $appointment,
            'canCancel' => $this->canPatientCancel($appointment),
            'canReschedule' => $this->canPatientReschedule($appointment),
        ]);
    }

    public function cancel(CancelAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        /** @var Patient $patient */
        $patient = Auth::guard('patient')->user();
        $this->assertOwnsAppointment($patient, $appointment);

        $validated = $request->validated();
        $reason = isset($validated['reason']) ? trim((string) $validated['reason']) : null;
        $reason = $reason !== '' ? $reason : null;

        try {
            $this->bookingService->cancel($appointment, $patient, $reason);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('cabinet.appointments.show', $appointment)
            ->with('status', 'Запись отменена.');
    }

    public function rescheduleForm(Appointment $appointment): View|RedirectResponse
    {
        /** @var Patient $patient */
        $patient = Auth::guard('patient')->user();
        $this->assertOwnsAppointment($patient, $appointment);

        if (! $this->canPatientReschedule($appointment)) {
            return redirect()
                ->route('cabinet.appointments.show', $appointment)
                ->with('error', 'Эту запись нельзя перенести.');
        }

        $service = $appointment->service;
        $doctor = $appointment->doctor;
        if ($service === null || $doctor === null) {
            return redirect()
                ->route('cabinet.appointments.show', $appointment)
                ->with('error', 'Некорректные данные записи.');
        }

        if (! $doctor->schedules()->exists()) {
            return redirect()
                ->route('cabinet.appointments.show', $appointment)
                ->with('error', 'У врача не настроено расписание.');
        }

        $dates = $this->bookingService->availableDates(
            $doctor,
            $service,
            CarbonInterval::days(30),
        );

        $selectedDateStr = $dates[0] ?? null;
        $slots = collect();

        if ($selectedDateStr !== null) {
            $day = CarbonImmutable::parse($selectedDateStr, config('app.timezone'))->startOfDay();
            $slots = $this->bookingService->availableSlots($doctor, $service, $day);
        }

        return view('booking.slot', [
            'service' => $service,
            'doctor' => $doctor,
            'availableDates' => $dates,
            'selectedDate' => $selectedDateStr,
            'slots' => $slots,
            'prefillStartAt' => null,
            'prefillNote' => null,
            'isReschedule' => true,
            'rescheduleAppointment' => $appointment,
        ]);
    }

    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        /** @var Patient $patient */
        $patient = Auth::guard('patient')->user();
        $this->assertOwnsAppointment($patient, $appointment);

        if (! $this->canPatientReschedule($appointment)) {
            return redirect()
                ->route('cabinet.appointments.show', $appointment)
                ->with('error', 'Эту запись нельзя перенести.');
        }

        $validated = $request->validated();
        $startsAt = CarbonImmutable::parse((string) $validated['start_at'], config('app.timezone'));

        $newDoctor = null;
        if (! empty($validated['doctor_id'])) {
            $newDoctor = Doctor::query()->active()->whereKey((int) $validated['doctor_id'])->firstOrFail();
            $service = $appointment->service;
            if ($service === null || ! $newDoctor->services()->where('services.id', $service->id)->exists()) {
                return back()->withInput()->withErrors(['doctor_id' => 'Врач не оказывает эту услугу.']);
            }
        }

        try {
            $newAppointment = $this->bookingService->reschedule($patient, $appointment, $startsAt, $newDoctor);
        } catch (BookingException $e) {
            return back()->withInput()->withErrors(['start_at' => $e->getMessage()]);
        }

        return redirect()
            ->route('cabinet.appointments.show', $newAppointment)
            ->with('status', 'Запись перенесена на новое время.');
    }

    public function editProfile(): View
    {
        /** @var Patient $patient */
        $patient = Auth::guard('patient')->user();

        return view('cabinet.profile', [
            'patient' => $patient,
        ]);
    }

    public function updateProfile(UpdateCabinetProfileRequest $request): RedirectResponse
    {
        /** @var Patient $patient */
        $patient = Auth::guard('patient')->user();

        $validated = $request->validated();
        $birth = Carbon::createFromFormat('d.m.Y', (string) $validated['birth_date'])->startOfDay();

        $email = isset($validated['email']) ? trim(strtolower((string) $validated['email'])) : '';
        $email = $email !== '' ? $email : null;

        $patient->forceFill([
            'last_name' => (string) $validated['last_name'],
            'first_name' => (string) $validated['first_name'],
            'middle_name' => filled($validated['middle_name'] ?? null)
                ? (string) $validated['middle_name']
                : null,
            'birth_date' => $birth,
            'gender' => (string) $validated['gender'],
            'email' => $email,
        ])->save();

        return redirect()
            ->route('cabinet.profile.edit')
            ->with('status', 'Профиль сохранён.');
    }

    private function assertOwnsAppointment(Patient $patient, Appointment $appointment): void
    {
        if ($appointment->patient_id !== $patient->id) {
            abort(404);
        }
    }

    private function canPatientCancel(Appointment $appointment): bool
    {
        if (! in_array($appointment->status, [AppointmentStatus::New, AppointmentStatus::Processing], true)) {
            return false;
        }

        $windowH = (int) Setting::getValue('booking', 'cancel_window_hours', '3');
        if ($appointment->start_at !== null && $appointment->start_at->lte(now()->addHours($windowH))) {
            return false;
        }

        return true;
    }

    private function canPatientReschedule(Appointment $appointment): bool
    {
        return in_array($appointment->status, [AppointmentStatus::New, AppointmentStatus::Processing], true);
    }
}
