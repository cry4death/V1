<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\PatientPhoneRequest;
use App\Http\Requests\Patient\StorePatientRegisterProfileRequest;
use App\Http\Requests\Patient\VerifyPatientOtpRequest;
use App\Jobs\SyncPatientToEspo;
use App\Models\Patient;
use App\Services\PatientOtpService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PatientRegisterController extends Controller
{
    private const SESSION_KEY = 'patient_register_profile';

    public function __construct(
        private readonly PatientOtpService $patientOtpService,
    ) {}

    public function showProfile(): View
    {
        $data = session(self::SESSION_KEY, []);

        return view('patient.register-profile', ['profile' => $data]);
    }

    public function storeProfile(StorePatientRegisterProfileRequest $request): RedirectResponse
    {
        session([self::SESSION_KEY => $request->validated()]);

        return redirect()->route('patient.register.phone');
    }

    public function showPhone(): View|RedirectResponse
    {
        if (! session()->has(self::SESSION_KEY)) {
            return redirect()->route('patient.register.profile')
                ->with('error', 'Сначала заполните личные данные.');
        }

        return view('patient.register-phone');
    }

    public function requestOtp(PatientPhoneRequest $request): RedirectResponse
    {
        if (! session()->has(self::SESSION_KEY)) {
            return redirect()->route('patient.register.profile')
                ->with('error', 'Сначала заполните личные данные.');
        }

        $phone = $request->validated('phone');

        if (Patient::query()->where('phone', $phone)->exists()) {
            return back()->withInput()->withErrors([
                'phone' => 'Этот номер уже зарегистрирован. Войдите.',
            ]);
        }

        session(['patient_register_phone' => $phone]);

        $this->patientOtpService->issue($phone);

        return redirect()->route('patient.register.otp')
            ->with('status', 'Код отправлен. Для теста используйте: 111111');
    }

    public function showOtp(): View|RedirectResponse
    {
        if (! session()->has(self::SESSION_KEY) || ! session()->has('patient_register_phone')) {
            return redirect()->route('patient.register.profile')
                ->with('error', 'Заполните данные и номер телефона.');
        }

        return view('patient.register-otp');
    }

    public function complete(VerifyPatientOtpRequest $request): RedirectResponse
    {
        if (! session()->has(self::SESSION_KEY) || ! session()->has('patient_register_phone')) {
            return redirect()->route('patient.register.profile')
                ->with('error', 'Сессия регистрации истекла. Начните снова.');
        }

        $phone = (string) session('patient_register_phone');
        $otp = (string) $request->validated('otp');

        if (! $this->patientOtpService->verifyAndConsume($phone, $otp)) {
            return back()->withErrors(['otp' => 'Неверный или просроченный код. Запросите новый.']);
        }

        $profile = session(self::SESSION_KEY, []);
        $birthRaw = (string) ($profile['birth_date'] ?? '');
        $birth = Carbon::createFromFormat('d.m.Y', $birthRaw)->startOfDay();

        $patient = Patient::query()->create([
            'phone' => $phone,
            'last_name' => (string) $profile['last_name'],
            'first_name' => (string) $profile['first_name'],
            'middle_name' => filled($profile['middle_name'] ?? null)
                ? (string) $profile['middle_name']
                : null,
            'birth_date' => $birth,
            'gender' => (string) $profile['gender'],
            'password' => Str::random(64),
        ]);

        session()->forget([self::SESSION_KEY, 'patient_register_phone']);

        Auth::guard('patient')->login($patient);
        $request->session()->regenerate();

        if (config('espo.enabled')) {
            DB::afterCommit(static function () use ($patient): void {
                SyncPatientToEspo::dispatch($patient->id);
            });
        }

        return redirect()->intended(route('cabinet.dashboard'))
            ->with('status', 'Регистрация завершена.');
    }
}
