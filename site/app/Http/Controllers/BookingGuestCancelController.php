<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Models\Appointment;
use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookingGuestCancelController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function cancel(Request $request, Appointment $appointment): View
    {
        $patient = $appointment->patient;
        if ($patient === null) {
            abort(404);
        }

        try {
            $this->bookingService->cancel($appointment, $patient, 'По ссылке из уведомления');
        } catch (BookingException $e) {
            return view('patient.booking.guest-cancel-result', [
                'error' => $e->getMessage(),
            ]);
        }

        return view('patient.booking.guest-cancel-result', [
            'success' => true,
        ]);
    }
}
