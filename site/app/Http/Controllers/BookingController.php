<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patient\StoreBookingRequest;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->active()
            ->with('direction')
            ->orderBy('name')
            ->get();

        $serviceSlug = request()->query('service');
        $selected = null;
        if (is_string($serviceSlug) && $serviceSlug !== '') {
            $selected = Service::query()
                ->active()
                ->where('slug', $serviceSlug)
                ->first();
        }

        $doctors = collect();
        if ($selected !== null) {
            $doctors = $selected->doctors()
                ->active()
                ->with('specialization')
                ->orderBy('sort_order')
                ->get();
        }

        return view('patient.booking', [
            'services' => $services,
            'selectedService' => $selected,
            'doctors' => $doctors,
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $patient = $request->user('patient');
        if ($patient === null) {
            return redirect()->route('patient.login');
        }

        $validated = $request->validated();
        $service = Service::query()->active()->whereKey($validated['service_id'])->firstOrFail();
        $doctor = Doctor::query()->active()->whereKey($validated['doctor_id'])->firstOrFail();

        $linked = $doctor->services()->where('services.id', $service->id)->exists();
        if (! $linked) {
            return back()->withInput()->withErrors([
                'doctor_id' => 'Выбранный врач не оказывает эту услугу.',
            ]);
        }

        DB::transaction(function () use ($patient, $service, $doctor) {
            $patient->appointments()->create([
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'patient_name' => $patient->displayName(),
                'phone' => $patient->phone,
                'email' => null,
                'type' => 'appointment',
                'status' => 'new',
                'preferred_date' => null,
                'message' => 'Онлайн-запись без выбора времени (черновик для проверки).',
            ]);
        });

        return redirect()->route('booking.index', ['service' => $service->slug])
            ->with('status', 'Заявка создана. Мы свяжемся с вами для уточнения времени.');
    }
}
