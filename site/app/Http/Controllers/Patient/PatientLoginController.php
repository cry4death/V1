<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\PatientPhoneRequest;
use App\Http\Requests\Patient\VerifyPatientOtpRequest;
use App\Models\Patient;
use App\Services\PatientOtpService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PatientLoginController extends Controller
{
    public function __construct(
        private readonly PatientOtpService $patientOtpService,
    ) {}

    public function show(): View
    {
        return view('patient.login');
    }

    public function requestOtp(PatientPhoneRequest $request): RedirectResponse
    {
        $phone = $request->validated('phone');

        $patient = Patient::query()->where('phone', $phone)->first();
        if ($patient === null) {
            return back()->withInput()->withErrors([
                'phone' => 'Номер не найден. Зарегистрируйтесь.',
            ]);
        }

        session(['patient_login_phone' => $phone]);

        $this->patientOtpService->issue($phone);

        return redirect()->route('patient.login.otp')
            ->with('status', 'Код отправлен. Для теста используйте: 111111');
    }

    public function showOtp(): View|RedirectResponse
    {
        if (! session()->has('patient_login_phone')) {
            return redirect()->route('patient.login')
                ->with('error', 'Сначала укажите телефон.');
        }

        return view('patient.login-otp');
    }

    public function verify(VerifyPatientOtpRequest $request): RedirectResponse
    {
        if (! session()->has('patient_login_phone')) {
            return redirect()->route('patient.login')
                ->with('error', 'Сессия истекла. Введите телефон снова.');
        }

        $phone = (string) session('patient_login_phone');
        $otp = (string) $request->validated('otp');

        if (! $this->patientOtpService->verifyAndConsume($phone, $otp)) {
            return back()->withErrors(['otp' => 'Неверный или просроченный код.']);
        }

        $patient = Patient::query()->where('phone', $phone)->firstOrFail();

        session()->forget('patient_login_phone');

        Auth::guard('patient')->login($patient);
        $request->session()->regenerate();

        return redirect()->intended(route('cabinet.dashboard'))
            ->with('status', 'Вы вошли.');
    }
}
