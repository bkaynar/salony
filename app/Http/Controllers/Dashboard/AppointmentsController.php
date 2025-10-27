<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointments;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AppointmentsController extends Controller
{
    /**
     * Display a listing of the salon's appointments.
     */
    public function index()
    {
        $user = Auth::user();

        // Make sure user has a salon
        if (! $user || ! $user->salon_id) {
            return Inertia::render('Dashboard/Appointments/Index', [
                'appointments' => [],
            ]);
        }

        $appointments = Appointments::with(['customer', 'staff', 'services'])
            ->where('salon_id', $user->salon_id)
            ->orderBy('start_time', 'desc')
            ->get();

        return Inertia::render('Dashboard/Appointments/Index', [
            'appointments' => $appointments,
        ]);
    }

    /**
     * Calendar view for appointments
     */
    public function calendar()
    {
        $user = Auth::user();

        if (! $user || ! $user->salon_id) {
            return Inertia::render('Dashboard/Appointments/Calendar', [
                'events' => [],
            ]);
        }

        $appointments = Appointments::with(['customer', 'staff'])
            ->where('salon_id', $user->salon_id)
            ->get();

        $events = $appointments->map(function ($a) {
            return [
                'id' => $a->id,
                'title' => ($a->customer?->name ?? 'Müşteri') . ' — ' . ($a->staff?->name ?? 'Personel'),
                'start' => $a->start_time?->toIso8601String(),
                'end' => $a->end_time?->toIso8601String(),
                'extendedProps' => [
                    'staff_id' => $a->staff_id,
                    'status' => $a->status,
                    'total_price' => $a->total_price,
                ],
            ];
        })->toArray();

        return Inertia::render('Dashboard/Appointments/Calendar', [
            'events' => $events,
        ]);
    }
}
