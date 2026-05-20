<?php

namespace App\Providers;

use App\Contracts\SmsSender;
use App\Models\Direction;
use App\Models\Setting;
use App\Services\BookingService;
use App\Services\Crm\EspoCrmSyncService;
use App\Services\Sms\LogSmsSender;
use App\Services\Sms\SmsBy;
use App\Tiptap\Highlight as SafeHighlight;
use Filament\Actions\Action;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Tiptap\Marks\Highlight as TiptapHighlight;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BookingService::class);

        $this->app->singleton(EspoCrmSyncService::class);

        $this->app->singleton(SmsSender::class, function ($app): SmsSender {
            return match (config('sms.driver')) {
                'sms_by' => $app->make(SmsBy::class),
                default => $app->make(LogSmsSender::class),
            };
        });

        // Tiptap-php Highlight (multicolor=true) падает в PHP 8.4 на старом контенте без data-color:
        // «Undefined property: stdClass::$color». Подменяем на свою null-safe версию.
        $this->app->bind(TiptapHighlight::class, SafeHighlight::class);
    }

    public function boot(): void
    {
        $isDevLike = app()->environment('local', 'testing');

        RateLimiter::for('otp-by-ip-phone', function (Request $request) use ($isDevLike): Limit {
            $phone = $request->input('phone');
            $keySuffix = is_string($phone) && $phone !== ''
                ? sha1($phone)
                : 'guest';

            return Limit::perMinute($isDevLike ? 60 : 6)->by(sha1($request->ip().'|'.$keySuffix));
        });

        RateLimiter::for('booking-web', function (Request $request) use ($isDevLike): Limit {
            return Limit::perMinute($isDevLike ? 2000 : 120)->by($request->ip());
        });

        RateLimiter::for('booking-slot', function (Request $request) use ($isDevLike): Limit {
            return Limit::perMinute($isDevLike ? 2000 : 200)->by($request->ip());
        });

        RateLimiter::for('booking-slot-intent', function (Request $request) use ($isDevLike): Limit {
            return Limit::perMinute($isDevLike ? 2000 : 90)->by($request->ip());
        });

        RateLimiter::for('booking-confirm', function (Request $request) use ($isDevLike): Limit {
            $patient = $request->user('patient');
            $key = $patient ? 'patient:'.$patient->id : 'ip:'.$request->ip();

            return Limit::perMinute($isDevLike ? 500 : 30)->by($key);
        });

        RateLimiter::for('cabinet', function (Request $request) use ($isDevLike): Limit {
            $patient = $request->user('patient');
            $key = $patient ? 'patient:'.$patient->id : 'ip:'.$request->ip();

            return Limit::perMinute($isDevLike ? 2000 : 100)->by($key);
        });

        RateLimiter::for('cabinet-action', function (Request $request) use ($isDevLike): Limit {
            $patient = $request->user('patient');
            $key = $patient ? 'patient:'.$patient->id : 'ip:'.$request->ip();

            return Limit::perMinute($isDevLike ? 500 : 30)->by($key);
        });

        RateLimiter::for('booking-guest-cancel', function (Request $request) use ($isDevLike): Limit {
            return Limit::perMinute($isDevLike ? 500 : 24)->by($request->ip());
        });

        /** Лимиты на публичные GET API мастера записи (слоты/даты): 60/мин легко исчерпываются при кликах по календарю → 429 без фикса. */
        RateLimiter::for('booking-api-catalog', function (Request $request) use ($isDevLike): Limit {
            return Limit::perMinute($isDevLike ? 5000 : 400)->by($request->ip());
        });

        FilamentAsset::register([
            Js::make(
                'rich-content-plugins/highlight',
                resource_path('js/dist/filament/rich-content-plugins/highlight.js'),
            )->loadedOnRequest(),
            Js::make(
                'rich-content-plugins/line-height',
                resource_path('js/dist/filament/rich-content-plugins/line-height.js'),
            )->loadedOnRequest(),
            Js::make(
                'rich-content-plugins/blockquote-auto-quotes',
                resource_path('js/dist/filament/rich-content-plugins/blockquote-auto-quotes.js'),
            )->loadedOnRequest(),
        ]);

        Action::configureUsing(function (Action $action): void {
            if ($action->getName() === 'textColor') {
                $action->modalSubmitActionLabel('Выбрать цвет');
            }
        });

        View::composer('*', function ($view) {
            try {
                $contacts = Setting::getGroup('contacts');
                $schedule = Setting::getGroup('schedule');
                $social = Setting::getGroup('social');
                $legal = Setting::getGroup('legal');
                $navDirections = Direction::orderBy('name')->get(['name', 'slug']);
            } catch (\Throwable $e) {
                $contacts = $schedule = $social = $legal = [];
                $navDirections = collect();
            }

            $view->with(compact('contacts', 'schedule', 'social', 'legal', 'navDirections'));
        });
    }
}
