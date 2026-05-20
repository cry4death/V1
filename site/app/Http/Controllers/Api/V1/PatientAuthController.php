<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequestOtpRequest;
use App\Http\Requests\Api\Auth\LoginVerifyRequest;
use App\Http\Requests\Api\Auth\RegisterRequestOtpRequest;
use App\Http\Requests\Api\Auth\RegisterVerifyRequest;
use App\Http\Resources\Api\PatientResource;
use App\Jobs\SyncPatientToEspo;
use App\Models\Patient;
use App\Services\PatientOtpService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PatientAuthController extends Controller
{
    private const REGISTER_CACHE_PREFIX = 'api_patient_register_v1:';

    /** Срок жизни refresh-токена в днях. */
    private const REFRESH_TTL_DAYS = 90;

    public function __construct(
        private readonly PatientOtpService $patientOtpService,
    ) {}

    public function registerRequestOtp(RegisterRequestOtpRequest $request): JsonResponse
    {
        $phone = (string) $request->validated('phone');

        if (Patient::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => ['Этот номер уже зарегистрирован. Войдите.'],
            ]);
        }

        $payload = $request->safe()->only(['last_name', 'first_name', 'middle_name', 'birth_date', 'gender']);
        Cache::put(self::REGISTER_CACHE_PREFIX.$phone, $payload, now()->addMinutes(15));

        $this->patientOtpService->issue($phone);

        return response()->json([
            'data' => [
                'message' => 'Код отправлен.',
            ],
        ]);
    }

    public function registerVerify(RegisterVerifyRequest $request): JsonResponse
    {
        $phone = (string) $request->validated('phone');
        $otp = (string) $request->validated('otp');

        $cached = Cache::get(self::REGISTER_CACHE_PREFIX.$phone);
        if (! is_array($cached)) {
            throw ValidationException::withMessages([
                'phone' => ['Сессия истекла. Запросите код снова.'],
            ]);
        }

        if (! $this->patientOtpService->verifyAndConsume($phone, $otp)) {
            throw ValidationException::withMessages([
                'otp' => ['Неверный или просроченный код. Запросите новый.'],
            ]);
        }

        Cache::forget(self::REGISTER_CACHE_PREFIX.$phone);

        if (Patient::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => ['Этот номер уже зарегистрирован.'],
            ]);
        }

        $patient = DB::transaction(function () use ($cached, $phone) {
            $birthRaw = (string) ($cached['birth_date'] ?? '');
            $birth = Carbon::createFromFormat('d.m.Y', $birthRaw)->startOfDay();

            return Patient::query()->create([
                'phone' => $phone,
                'last_name' => (string) $cached['last_name'],
                'first_name' => (string) $cached['first_name'],
                'middle_name' => filled($cached['middle_name'] ?? null)
                    ? (string) $cached['middle_name']
                    : null,
                'birth_date' => $birth,
                'gender' => (string) $cached['gender'],
                'password' => Str::random(64),
            ]);
        });

        [$accessToken, $refreshToken] = $this->issueTokens($patient);

        if (config('espo.enabled')) {
            DB::afterCommit(static function () use ($patient): void {
                SyncPatientToEspo::dispatch($patient->id);
            });
        }

        return response()->json([
            'data' => [
                'token' => $accessToken,
                'refresh_token' => $refreshToken,
                'patient' => PatientResource::make($patient)->resolve(),
            ],
        ]);
    }

    public function loginRequestOtp(LoginRequestOtpRequest $request): JsonResponse
    {
        $phone = (string) $request->validated('phone');

        $patient = Patient::query()->where('phone', $phone)->first();
        if ($patient === null) {
            throw ValidationException::withMessages([
                'phone' => ['Номер не найден'],
            ]);
        }

        $this->patientOtpService->issue($phone);

        return response()->json([
            'data' => [
                'message' => 'Код отправлен.',
            ],
        ]);
    }

    public function loginVerify(LoginVerifyRequest $request): JsonResponse
    {
        $phone = (string) $request->validated('phone');
        $otp = (string) $request->validated('otp');

        $patient = Patient::query()->where('phone', $phone)->first();
        if ($patient === null) {
            throw ValidationException::withMessages([
                'phone' => ['Номер не найден'],
            ]);
        }

        if (! $this->patientOtpService->verifyAndConsume($phone, $otp)) {
            throw ValidationException::withMessages([
                'otp' => ['Неверный или просроченный код. Запросите новый.'],
            ]);
        }

        [$accessToken, $refreshToken] = $this->issueTokens($patient);

        if (config('espo.enabled')) {
            DB::afterCommit(static function () use ($patient): void {
                SyncPatientToEspo::dispatch($patient->id);
            });
        }

        return response()->json([
            'data' => [
                'token' => $accessToken,
                'refresh_token' => $refreshToken,
                'patient' => PatientResource::make($patient)->resolve(),
            ],
        ]);
    }

    /**
     * POST /auth/refresh
     *
     * Принимает refresh_token, возвращает новые access + refresh токены (ротация).
     * Старый access-токен отзывается; refresh-токен обновляется.
     */
    public function refresh(Request $request): JsonResponse
    {
        $raw = (string) $request->input('refresh_token', '');

        if ($raw === '') {
            return response()->json(['message' => 'refresh_token обязателен.'], 422);
        }

        $hashed = hash('sha256', $raw);

        /** @var Patient|null $patient */
        $patient = Patient::query()
            ->where('refresh_token', $hashed)
            ->first();

        if ($patient === null) {
            return response()->json(['message' => 'Недействительный refresh_token.'], 401);
        }

        if ($patient->refresh_token_expires_at === null || $patient->refresh_token_expires_at->isPast()) {
            // Инвалидируем истёкший токен
            $patient->forceFill(['refresh_token' => null, 'refresh_token_expires_at' => null])->save();

            return response()->json(['message' => 'refresh_token истёк. Войдите заново.'], 401);
        }

        // Отзываем текущий access-токен (если запрос пришёл с ним)
        $request->user()?->currentAccessToken()?->delete();

        [$accessToken, $refreshToken] = $this->issueTokens($patient);

        return response()->json([
            'data' => [
                'token' => $accessToken,
                'refresh_token' => $refreshToken,
            ],
        ]);
    }

    /**
     * Создаёт новый Sanctum access-токен и ротирует refresh-токен.
     *
     * @return array{0: string, 1: string} [plaintext access token, plaintext refresh token]
     */
    private function issueTokens(Patient $patient): array
    {
        // Access token — Sanctum personal access token
        $accessToken = $patient->createToken('mobile')->plainTextToken;

        // Refresh token — 64 случайных байта, хранится SHA-256 хешем
        $rawRefresh = Str::random(64);

        $patient->forceFill([
            'refresh_token' => hash('sha256', $rawRefresh),
            'refresh_token_expires_at' => now()->addDays(self::REFRESH_TTL_DAYS),
        ])->save();

        return [$accessToken, $rawRefresh];
    }
}
