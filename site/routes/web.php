<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Patient\PatientLoginController;
use App\Http\Controllers\Patient\PatientLogoutController;
use App\Http\Controllers\Patient\PatientRegisterController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaticPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
Route::get('/doctors/{slug}', [DoctorController::class, 'show'])->name('doctors.show');
Route::post('/doctors/{slug}/reviews', [DoctorController::class, 'storeReview'])->name('reviews.store');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
Route::get('/promotions/{slug}', [PromotionController::class, 'show'])->name('promotions.show');

Route::get('/contacts', [ContactController::class, 'index'])->name('contacts');

Route::get('/about', [StaticPageController::class, 'about'])->name('about');
Route::get('/documents', [StaticPageController::class, 'documents'])->name('documents');
Route::get('/vacancies', [StaticPageController::class, 'vacancies'])->name('vacancies');
Route::get('/insurance', [StaticPageController::class, 'insurance'])->name('insurance');
Route::get('/medical-device', [StaticPageController::class, 'medicalDevice'])->name('medical-device');
Route::get('/equipment/{slug}', [EquipmentController::class, 'show'])->name('equipment.show');
Route::get('/search', [StaticPageController::class, 'search'])->name('search');

Route::prefix('patient')->name('patient.')->group(function (): void {
    Route::get('/register', [PatientRegisterController::class, 'showProfile'])->name('register.profile');
    Route::post('/register/profile', [PatientRegisterController::class, 'storeProfile'])->name('register.profile.store');
    Route::get('/register/phone', [PatientRegisterController::class, 'showPhone'])->name('register.phone');
    Route::post('/register/request-otp', [PatientRegisterController::class, 'requestOtp'])
        ->middleware('throttle:12,1')
        ->name('register.request-otp');
    Route::get('/register/otp', [PatientRegisterController::class, 'showOtp'])->name('register.otp');
    Route::post('/register/complete', [PatientRegisterController::class, 'complete'])->name('register.complete');

    Route::get('/login', [PatientLoginController::class, 'show'])->name('login');
    Route::post('/login/request-otp', [PatientLoginController::class, 'requestOtp'])
        ->middleware('throttle:12,1')
        ->name('login.request-otp');
    Route::get('/login/otp', [PatientLoginController::class, 'showOtp'])->name('login.otp');
    Route::post('/login/verify', [PatientLoginController::class, 'verify'])->name('login.verify');
});

Route::middleware(['patient.auth', 'throttle:30,1'])->group(function (): void {
    Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::post('/patient/logout', PatientLogoutController::class)->name('patient.logout');
});
