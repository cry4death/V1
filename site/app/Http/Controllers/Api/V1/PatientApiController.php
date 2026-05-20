<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Patient\UpdatePatientProfileRequest;
use App\Http\Resources\Api\PatientResource;
use App\Jobs\SyncPatientToEspo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientApiController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => PatientResource::make($request->user())->resolve(),
        ]);
    }

    public function update(UpdatePatientProfileRequest $request): JsonResponse
    {
        $patient = $request->user();
        $validated = $request->validated();

        if (array_key_exists('birth_date', $validated) && is_string($validated['birth_date'])) {
            $validated['birth_date'] = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->startOfDay();
        }

        $patient->fill($validated);
        $patient->save();

        if (config('espo.enabled')) {
            DB::afterCommit(static function () use ($patient): void {
                SyncPatientToEspo::dispatch($patient->id);
            });
        }

        return response()->json([
            'data' => PatientResource::make($patient->fresh())->resolve(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $patient = $request->user();

        // Отзываем текущий access-токен
        $patient->currentAccessToken()?->delete();

        // Инвалидируем refresh-токен — следующий вход только по OTP
        $patient->forceFill([
            'refresh_token' => null,
            'refresh_token_expires_at' => null,
        ])->save();

        return response()->json([
            'data' => ['message' => 'Выход выполнен.'],
        ]);
    }
}
