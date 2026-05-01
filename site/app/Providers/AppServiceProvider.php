<?php

namespace App\Providers;

use App\Models\Direction;
use App\Models\Setting;
use App\Tiptap\Highlight as SafeHighlight;
use Filament\Actions\Action;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Tiptap\Marks\Highlight as TiptapHighlight;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Tiptap-php Highlight (multicolor=true) падает в PHP 8.4 на старом контенте без data-color:
        // «Undefined property: stdClass::$color». Подменяем на свою null-safe версию.
        $this->app->bind(TiptapHighlight::class, SafeHighlight::class);
    }

    public function boot(): void
    {
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
                $navDirections = Direction::orderBy('sort_order')->get(['name', 'slug']);
            } catch (\Throwable $e) {
                $contacts = $schedule = $social = $legal = [];
                $navDirections = collect();
            }

            $view->with(compact('contacts', 'schedule', 'social', 'legal', 'navDirections'));
        });
    }
}
