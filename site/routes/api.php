<?php

use App\Http\Controllers\Api\V1\AppointmentApiController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\BookingCatalogController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\DoctorController;
use App\Http\Controllers\Api\V1\PatientApiController;
use App\Http\Controllers\Api\V1\PatientAuthController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\ServiceDirectionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register/request-otp', [PatientAuthController::class, 'registerRequestOtp'])
        ->middleware('throttle:otp-by-ip-phone');
    Route::post('/register/verify', [PatientAuthController::class, 'registerVerify'])
        ->middleware('throttle:12,1');
    Route::post('/login/request-otp', [PatientAuthController::class, 'loginRequestOtp'])
        ->middleware('throttle:otp-by-ip-phone');
    Route::post('/login/verify', [PatientAuthController::class, 'loginVerify'])
        ->middleware('throttle:12,1');
    Route::post('/refresh', [PatientAuthController::class, 'refresh'])
        ->middleware('throttle:20,1');
});

Route::middleware('throttle:booking-api-catalog')->group(function (): void {
    Route::get('/booking/services', [BookingCatalogController::class, 'services']);
    Route::get('/booking/doctors', [BookingCatalogController::class, 'doctors']);
    Route::get('/booking/slots', [BookingCatalogController::class, 'slots']);
    Route::get('/booking/dates', [BookingCatalogController::class, 'dates']);
});

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::middleware('throttle:30,1')->post('/devices/register', [DeviceController::class, 'register']);

    Route::middleware('throttle:10,1')->post('/appointments', [AppointmentApiController::class, 'store']);

    Route::middleware('throttle:60,1')->group(function (): void {
        Route::get('/me', [PatientApiController::class, 'me']);
        Route::patch('/me', [PatientApiController::class, 'update']);
        Route::post('/me/logout', [PatientApiController::class, 'logout']);

        Route::get('/appointments', [AppointmentApiController::class, 'index']);
        Route::get('/appointments/{appointment}', [AppointmentApiController::class, 'show']);
        Route::post('/appointments/{appointment}/cancel', [AppointmentApiController::class, 'cancel']);
        Route::post('/appointments/{appointment}/reschedule', [AppointmentApiController::class, 'reschedule']);
    });
});

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{slug}', [DoctorController::class, 'show'])->where('slug', '[a-z0-9\-]+');

Route::get('/service-directions', [ServiceDirectionController::class, 'index']);
Route::get('/service-directions/{slug}/services', [ServiceDirectionController::class, 'services'])
    ->where('slug', '[a-z0-9\-]+');

Route::get('/article-categories', [ArticleController::class, 'categories']);
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->where('slug', '[a-z0-9\-]+');

Route::get('/promotions', [PromotionController::class, 'index']);
Route::get('/promotions/{slug}', [PromotionController::class, 'show'])->where('slug', '[a-z0-9\-]+');
