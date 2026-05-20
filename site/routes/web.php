<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingFlowController;
use App\Http\Controllers\BookingGuestCancelController;
use App\Http\Controllers\Cabinet\CabinetController;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
Route::get('/doctors/{slug}', [DoctorController::class, 'show'])->name('doctors.show');
Route::post('/doctors/{slug}/reviews', [DoctorController::class, 'storeReview'])->name('reviews.store');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/direction/{slug}', [ServiceController::class, 'direction'])->name('services.direction');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
Route::get('/promotions/{slug}', [PromotionController::class, 'show'])->name('promotions.show');

Route::get('/contacts', [ContactController::class, 'index'])->name('contacts');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');

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
        ->middleware('throttle:otp-by-ip-phone')
        ->name('register.request-otp');
    Route::get('/register/otp', [PatientRegisterController::class, 'showOtp'])->name('register.otp');
    Route::post('/register/complete', [PatientRegisterController::class, 'complete'])->name('register.complete');

    Route::get('/login', [PatientLoginController::class, 'show'])->name('login');
    Route::post('/login/request-otp', [PatientLoginController::class, 'requestOtp'])
        ->middleware('throttle:otp-by-ip-phone')
        ->name('login.request-otp');
    Route::get('/login/otp', [PatientLoginController::class, 'showOtp'])->name('login.otp');
    Route::post('/login/verify', [PatientLoginController::class, 'verify'])->name('login.verify');
});

Route::get('/booking/guest-cancel/{appointment}', [BookingGuestCancelController::class, 'cancel'])
    ->middleware(['signed', 'throttle:booking-guest-cancel'])
    ->name('booking.guest-cancel');

Route::prefix('booking')->name('booking.')->group(function (): void {
    Route::middleware(['throttle:booking-web'])->group(function (): void {
        Route::get('/start', [BookingFlowController::class, 'start'])->name('start');
        Route::get('/entry/doctors', [BookingFlowController::class, 'browseBookingDoctors'])->name('browseDoctors');
        Route::get('/service', [BookingFlowController::class, 'pickService'])->name('pickService');
        Route::get('/doctor', [BookingFlowController::class, 'pickDoctor'])->name('pickDoctor');
    });
    Route::get('/slot', [BookingFlowController::class, 'pickSlot'])
        ->middleware(['throttle:booking-slot'])
        ->name('pickSlot');
    Route::post('/slot-intent', [BookingFlowController::class, 'rememberSlotIntent'])
        ->middleware(['throttle:booking-slot-intent'])
        ->name('slotIntent');
    Route::middleware(['patient.auth', 'throttle:booking-confirm'])->post('/confirm', [BookingFlowController::class, 'confirm'])->name('confirm');
});

Route::get('/booking', function (Request $request): RedirectResponse {
    if ($request->filled('doctor')) {
        return redirect()->route('booking.start', ['from' => 'doctor:'.$request->query('doctor')]);
    }
    if ($request->filled('service')) {
        return redirect()->route('booking.start', ['from' => 'service:'.$request->query('service')]);
    }

    return redirect()->route('booking.start');
})->middleware(['throttle:booking-web'])->name('booking.index');

Route::middleware(['patient.auth', 'throttle:cabinet'])->prefix('cabinet')->name('cabinet.')->group(function (): void {
    Route::get('/', [CabinetController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [CabinetController::class, 'appointments'])->name('appointments.index');
    Route::get('/profile', [CabinetController::class, 'editProfile'])->name('profile.edit');
    Route::patch('/profile', [CabinetController::class, 'updateProfile'])->name('profile.update');

    Route::get('/appointments/{appointment}', [CabinetController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{appointment}/cancel', [CabinetController::class, 'cancel'])
        ->middleware('throttle:cabinet-action')
        ->name('appointments.cancel');
    Route::get('/appointments/{appointment}/reschedule', [CabinetController::class, 'rescheduleForm'])
        ->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/reschedule', [CabinetController::class, 'reschedule'])
        ->middleware('throttle:cabinet-action')
        ->name('appointments.reschedule.store');
});

Route::middleware(['patient.auth', 'throttle:cabinet'])->group(function (): void {
    Route::post('/patient/logout', PatientLogoutController::class)->name('patient.logout');
});
