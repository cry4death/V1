<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Api\Appointment\RescheduleAppointmentRequest;
use App\Http\Requests\Api\Appointment\StoreAppointmentRequest;
use App\Http\Resources\Api\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppointmentApiController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var Patient $patient */
        $patient = $request->user();

        $status = $request->query('status');
        $query = $patient->appointments()->with(['doctor.specialization', 'service.direction']);

        if ($status === 'upcoming') {
            $query->where('start_at', '>=', now())
                ->whereNotIn('status', [
                    AppointmentStatus::Cancelled,
                    AppointmentStatus::Rescheduled,
                ]);
            $query->orderBy('start_at');
        } elseif ($status === 'past') {
            $query->where(function ($q): void {
                $q->where('start_at', '<', now())
                    ->orWhereIn('status', [
                        AppointmentStatus::Completed,
                        AppointmentStatus::Cancelled,
                        AppointmentStatus::Rescheduled,
                    ]);
            });
            $query->orderByDesc('start_at');
        } else {
            $query->orderByDesc('start_at');
        }

        return AppointmentResource::collection($query->get());
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        /** @var Patient $patient */
        $patient = $request->user();
        $validated = $request->validated();

        $service = Service::query()->active()->whereKey((int) $validated['service_id'])->firstOrFail();
        $doctor = Doctor::query()->active()->whereKey((int) $validated['doctor_id'])->firstOrFail();

        $startsAt = CarbonImmutable::parse((string) $validated['start_at'], config('app.timezone'));
        $note = isset($validated['note']) ? trim((string) $validated['note']) : '';
        $note = $note !== '' ? $note : null;

        $appointment = $this->bookingService->book($patient, $doctor, $service, $startsAt, $note, AppointmentSource::Mobile);

        $appointment->load(['doctor.specialization', 'service.direction']);

        return response()->json([
            'data' => AppointmentResource::make($appointment)->resolve(),
        ], 201);
    }

    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointment($request, $appointment);
        $appointment->load(['doctor.specialization', 'service.direction']);

        return response()->json([
            'data' => AppointmentResource::make($appointment)->resolve(),
        ]);
    }

    public function cancel(CancelAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointment($request, $appointment);

        /** @var Patient $patient */
        $patient = $request->user();
        $validated = $request->validated();
        $reason = isset($validated['reason']) ? trim((string) $validated['reason']) : null;
        $reason = $reason !== '' ? $reason : null;

        $this->bookingService->cancel($appointment, $patient, $reason);
        $appointment->refresh()->load(['doctor.specialization', 'service.direction']);

        return response()->json([
            'data' => AppointmentResource::make($appointment)->resolve(),
        ]);
    }

    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointment($request, $appointment);

        /** @var Patient $patient */
        $patient = $request->user();
        $validated = $request->validated();

        $startsAt = CarbonImmutable::parse((string) $validated['start_at'], config('app.timezone'));
        $newDoctor = null;
        if (! empty($validated['doctor_id'])) {
            $newDoctor = Doctor::query()->active()->whereKey((int) $validated['doctor_id'])->firstOrFail();
            $service = $appointment->service;
            if ($service === null || ! $newDoctor->services()->where('services.id', $service->id)->exists()) {
                throw new BookingException('Врач не оказывает эту услугу.');
            }
        }

        $newAppointment = $this->bookingService->reschedule($patient, $appointment, $startsAt, $newDoctor);
        $newAppointment->load(['doctor.specialization', 'service.direction']);

        return response()->json([
            'data' => AppointmentResource::make($newAppointment)->resolve(),
        ]);
    }

    private function authorizeAppointment(Request $request, Appointment $appointment): void
    {
        /** @var Patient $patient */
        $patient = $request->user();
        if ($appointment->patient_id !== $patient->id) {
            abort(404);
        }
    }
}
