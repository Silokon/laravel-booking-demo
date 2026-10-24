<?php

namespace App\Http\Controllers;

use App\Actions\CreateAppointment;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Service;

class BookingController extends Controller
{
    public function index()
    {
        return view('booking.index', [
            'services' => Service::where('is_active', true)->orderBy('price')->get(),
        ]);
    }

    public function store(StoreAppointmentRequest $request, CreateAppointment $create)
    {
        $appointment = $create->handle($request->validated());

        return redirect()->route('booking.index')->with('booked', [
            'service' => $appointment->service->name,
            'master' => $appointment->master->name,
            'when' => $appointment->starts_at->translatedFormat('j F, H:i'),
        ]);
    }
}
