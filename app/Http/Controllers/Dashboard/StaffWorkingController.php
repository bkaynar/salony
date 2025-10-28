<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StaffWorking;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffWorkingController extends Controller
{
    /**
     * Display staff working hours
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user || ! $user->salon_id) {
            return Inertia::render('Dashboard/StaffWorking/Index', [
                'staff' => [],
                'workingHours' => [],
            ]);
        }

        // Get staff members
        $staffQuery = User::where('salon_id', $user->salon_id)
            ->where('is_bookable', true);

        if ($user->hasRole('staff')) {
            // If user is staff, only show their own schedule
            $staffQuery->where('id', $user->id);
        }

        $staff = $staffQuery->get(['id', 'name']);

        // Get working hours
        $workingHoursQuery = StaffWorking::with('user')
            ->whereHas('user', function ($query) use ($user) {
                $query->where('salon_id', $user->salon_id);
            });

        if ($user->hasRole('staff')) {
            $workingHoursQuery->where('user_id', $user->id);
        }

        $workingHours = $workingHoursQuery
            ->orderBy('user_id')
            ->orderBy('day_of_week')
            ->get()
            ->map(function ($wh) {
                return [
                    'id' => $wh->id,
                    'user_id' => $wh->user_id,
                    'user_name' => $wh->user?->name,
                    'day_of_week' => $wh->day_of_week,
                    'start_time' => $wh->start_time,
                    'end_time' => $wh->end_time,
                    'is_off' => $wh->is_off,
                ];
            });

        return Inertia::render('Dashboard/StaffWorking/Index', [
            'staff' => $staff,
            'workingHours' => $workingHours,
        ]);
    }

    /**
     * Store working hours
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required_if:is_off,false|nullable|date_format:H:i',
            'end_time' => 'required_if:is_off,false|nullable|date_format:H:i|after:start_time',
            'is_off' => 'boolean',
        ]);

        // Check if user belongs to same salon
        $staffUser = User::find($validated['user_id']);
        if ($staffUser->salon_id !== $user->salon_id) {
            abort(403, 'Bu personel size ait değil');
        }

        // Check if entry already exists
        $existing = StaffWorking::where('user_id', $validated['user_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->first();

        if ($existing) {
            return back()->withErrors(['exists' => 'Bu gün için zaten bir kayıt var!']);
        }

        StaffWorking::create($validated);

        return back()->with('success', 'Çalışma saati eklendi');
    }

    /**
     * Update working hours
     */
    public function update(Request $request, StaffWorking $staffWorking)
    {
        $user = Auth::user();

        // Check permission
        if ($staffWorking->user->salon_id !== $user->salon_id) {
            abort(403);
        }

        $validated = $request->validate([
            'start_time' => 'required_if:is_off,false|nullable|date_format:H:i',
            'end_time' => 'required_if:is_off,false|nullable|date_format:H:i|after:start_time',
            'is_off' => 'sometimes|boolean',
        ]);

        $staffWorking->update($validated);

        return back()->with('success', 'Çalışma saati güncellendi');
    }

    /**
     * Delete working hours
     */
    public function destroy(StaffWorking $staffWorking)
    {
        $user = Auth::user();

        // Check permission
        if ($staffWorking->user->salon_id !== $user->salon_id) {
            abort(403);
        }

        $staffWorking->delete();

        return back()->with('success', 'Çalışma saati silindi');
    }

    /**
     * Bulk update for a staff member
     */
    public function bulkUpdate(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule' => 'required|array',
            'schedule.*.day_of_week' => 'required|integer|between:0,6',
            'schedule.*.start_time' => 'nullable|date_format:H:i',
            'schedule.*.end_time' => 'nullable|date_format:H:i',
            'schedule.*.is_off' => 'required|boolean',
        ]);

        // Check if user belongs to same salon
        $staffUser = User::find($validated['user_id']);
        if ($staffUser->salon_id !== $user->salon_id) {
            abort(403, 'Bu personel size ait değil');
        }

        // Delete existing schedule
        StaffWorking::where('user_id', $validated['user_id'])->delete();

        // Create new schedule
        foreach ($validated['schedule'] as $day) {
            if (!$day['is_off']) {
                StaffWorking::create([
                    'user_id' => $validated['user_id'],
                    'day_of_week' => $day['day_of_week'],
                    'start_time' => $day['start_time'],
                    'end_time' => $day['end_time'],
                    'is_off' => false,
                ]);
            } else {
                StaffWorking::create([
                    'user_id' => $validated['user_id'],
                    'day_of_week' => $day['day_of_week'],
                    'start_time' => null,
                    'end_time' => null,
                    'is_off' => true,
                ]);
            }
        }

        return back()->with('success', 'Çalışma programı güncellendi');
    }
}
