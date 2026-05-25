<?php

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('mobile register request-otp and verify creates patient and sanctum token', function (): void {
    $this->postJson('/api/v1/auth/register/request-otp', [
        'last_name' => 'Иванов',
        'first_name' => 'Иван',
        'middle_name' => 'Иванович',
        'birth_date' => '01.01.1990',
        'gender' => 'male',
        'phone' => '375291234567',
    ])->assertOk()
        ->assertJsonPath('data.message', 'Код отправлен.');

    $this->postJson('/api/v1/auth/register/verify', [
        'phone' => '375291234567',
        'otp' => '111111',
    ])->assertOk()
        ->assertJsonStructure(['data' => ['token', 'refresh_token', 'patient']]);

    $patient = Patient::query()->where('phone', '375291234567')->first();
    expect($patient)->not->toBeNull();
    expect($patient->tokens)->toHaveCount(1);
    expect($patient->tokens()->first()->expires_at)->not->toBeNull();
    expect($patient->refresh_token)->not->toBeNull();
    expect($patient->refresh_token_expires_at)->not->toBeNull();
});

test('mobile register verify fails without prior request-otp payload in cache', function (): void {
    $this->postJson('/api/v1/auth/register/verify', [
        'phone' => '375291111111',
        'otp' => '111111',
    ])->assertStatus(422);
});

test('register request-otp rejects phone that is not belarus 375 plus nine digits', function (): void {
    $this->postJson('/api/v1/auth/register/request-otp', [
        'last_name' => 'Петров',
        'first_name' => 'Пётр',
        'middle_name' => null,
        'birth_date' => '02.02.1991',
        'gender' => 'male',
        'phone' => '29199988',
    ])->assertStatus(422);
});

test('register request-otp accepts nine-digit national number with 375 prefix added', function (): void {
    $this->postJson('/api/v1/auth/register/request-otp', [
        'last_name' => 'Сидоров',
        'first_name' => 'Сидор',
        'middle_name' => null,
        'birth_date' => '03.03.1992',
        'gender' => 'male',
        'phone' => '291888777',
    ])->assertOk()
        ->assertJsonPath('data.message', 'Код отправлен.');
});

test('register verify still accepts national nine digits with 375 prefix rule', function (): void {
    $this->postJson('/api/v1/auth/register/request-otp', [
        'last_name' => 'Петров',
        'first_name' => 'Пётр',
        'middle_name' => null,
        'birth_date' => '02.02.1991',
        'gender' => 'male',
        'phone' => '375291999888',
    ])->assertOk();

    $this->postJson('/api/v1/auth/register/verify', [
        'phone' => '291999888',
        'otp' => '111111',
    ])->assertOk();

    $patient = Patient::query()->where('phone', '375291999888')->first();
    expect($patient)->not->toBeNull();
});

test('me requires sanctum token', function (): void {
    $this->getJson('/api/v1/me')->assertUnauthorized();
});

test('me returns patient with bearer token', function (): void {
    $patient = Patient::query()->create([
        'phone' => '375291234567',
        'last_name' => 'Тестов',
        'first_name' => 'Тест',
        'middle_name' => null,
        'birth_date' => '1990-05-15',
        'gender' => 'male',
        'password' => bcrypt('unused'),
    ]);

    $token = $patient->createToken('mobile')->plainTextToken;

    $this->getJson('/api/v1/me', [
        'Authorization' => 'Bearer '.$token,
    ])->assertOk()
        ->assertJsonPath('data.phone', '375291234567');
});

test('mobile refresh rotates tokens and expires access token', function (): void {
    $this->postJson('/api/v1/auth/register/request-otp', [
        'last_name' => 'Иванов',
        'first_name' => 'Иван',
        'middle_name' => 'Иванович',
        'birth_date' => '01.01.1990',
        'gender' => 'male',
        'phone' => '375291234567',
    ])->assertOk();

    $login = $this->postJson('/api/v1/auth/register/verify', [
        'phone' => '375291234567',
        'otp' => '111111',
    ])->assertOk();

    $patient = Patient::query()->where('phone', '375291234567')->firstOrFail();
    $firstRefreshToken = $login->json('data.refresh_token');
    $firstRefreshTokenHash = $patient->refresh_token;

    expect($firstRefreshToken)->toBeString();
    expect($firstRefreshTokenHash)->toBeString();
    expect($patient->tokens)->toHaveCount(1);
    expect($patient->tokens()->first()->expires_at)->not->toBeNull();

    $refresh = $this->postJson('/api/v1/auth/refresh', [
        'refresh_token' => $firstRefreshToken,
    ])->assertOk()
        ->assertJsonStructure(['data' => ['token', 'refresh_token']]);

    $patient->refresh();
    $newAccessToken = $patient->tokens()->orderByDesc('id')->first();

    expect($refresh->json('data.token'))->toBeString();
    expect($refresh->json('data.refresh_token'))->toBeString();
    expect($patient->refresh_token)->not->toBe($firstRefreshTokenHash);
    expect($newAccessToken)->not->toBeNull();
    expect($newAccessToken->expires_at)->not->toBeNull();
});
