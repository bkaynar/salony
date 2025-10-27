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

        $query = Appointments::with(['customer', 'staff', 'services'])
            ->where('salon_id', $user->salon_id);

        if ($user->hasRole('staff')) {
            $query->where('staff_id', $user->id);
        }

        $appointments = $query->orderBy('start_time', 'desc')
            ->get();

        return Inertia::render('Dashboard/Appointments/Index', [
            'appointments' => $appointments,
        ]);
    }

}
