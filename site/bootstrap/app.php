<?php

use App\Console\Commands\SendAppointmentReminders;
use App\Exceptions\BookingException;
use App\Http\Middleware\EnsurePatientAuthenticated;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withSchedule(function (Schedule $schedule): void {
        // Напоминания о приёме: каждый час ищем записи через ~24 ч и шлём FCM-пуш
        $schedule->command(SendAppointmentReminders::class)->hourly();
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'patient.auth' => EnsurePatientAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (BookingException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => [
                        'booking' => [$e->getMessage()],
                    ],
                ], 422);
            }

            return null;
        });
    })->create();
