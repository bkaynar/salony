<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StaffTimeOffs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffTimeOffsController extends Controller
{
    /**
     * Display staff time offs list
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user || ! $user->salon_id) {
            return Inertia::render('Dashboard/StaffTimeOffs/Index', [
                'timeOffs' => [],
                'staff' => [],
            ]);
        }

        // Get staff members for this salon
        $staffQuery = User::where('salon_id', $user->salon_id)
            ->where('is_bookable', true);

        if ($user->hasRole('staff')) {
            // If user is staff, only show their own time offs
            $staffQuery->where('id', $user->id);
        }

        $staff = $staffQuery->get(['id', 'name']);

        // Get time offs
        $timeOffsQuery = StaffTimeOffs::with('user')
            ->whereHas('user', function ($query) use ($user) {
                $query->where('salon_id', $user->salon_id);
            });

        if ($user->hasRole('staff')) {
            // If user is staff, only show their own time offs
            $timeOffsQuery->where('user_id', $user->id);
        }

        $timeOffs = $timeOffsQuery
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($timeOff) {
                return [
                    'id' => $timeOff->id,
                    'user_id' => $timeOff->user_id,
                    'user_name' => $timeOff->user?->name,
                    'start_time' => $timeOff->start_time->format('Y-m-d H:i:s'),
                    'end_time' => $timeOff->end_time->format('Y-m-d H:i:s'),
                    'start_date_formatted' => $timeOff->start_time->format('d.m.Y H:i'),
                    'end_date_formatted' => $timeOff->end_time->format('d.m.Y H:i'),
                    'reason' => $timeOff->reason,
                ];
            });

        return Inertia::render('Dashboard/StaffTimeOffs/Index', [
            'timeOffs' => $timeOffs,
            'staff' => $staff,
        ]);
    }

    /**
     * Store a new time off
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'reason' => 'nullable|string|max:500',
        ]);

        // Check if user belongs to same salon
        $staffUser = User::find($validated['user_id']);
        if ($staffUser->salon_id !== $user->salon_id) {
            abort(403, 'Bu personel size ait değil');
        }

        // Check for overlapping time offs
        $overlapping = StaffTimeOffs::where('user_id', $validated['user_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($overlapping) {
            return back()->withErrors(['overlap' => 'Bu tarih aralığında zaten bir izin kaydı var!']);
        }

        StaffTimeOffs::create($validated);

        return back()->with('success', 'İzin kaydedildi');
    }

    /**
     * Update a time off
     */
    public function update(Request $request, StaffTimeOffs $timeOff)
    {
        $user = Auth::user();

        // Check permission
        if ($timeOff->user->salon_id !== $user->salon_id) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'reason' => 'nullable|string|max:500',
        ]);

        // Check if user belongs to same salon
        if (isset($validated['user_id'])) {
            $staffUser = User::find($validated['user_id']);
            if ($staffUser->salon_id !== $user->salon_id) {
                abort(403, 'Bu personel size ait değil');
            }
        }

        // Check for overlapping time offs (excluding current one)
        if (isset($validated['start_time']) || isset($validated['end_time'])) {
            $startTime = $validated['start_time'] ?? $timeOff->start_time->format('Y-m-d H:i:s');
            $endTime = $validated['end_time'] ?? $timeOff->end_time->format('Y-m-d H:i:s');
            $userId = $validated['user_id'] ?? $timeOff->user_id;

            $overlapping = StaffTimeOffs::where('user_id', $userId)
                ->where('id', '!=', $timeOff->id)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->exists();

            if ($overlapping) {
                return back()->withErrors(['overlap' => 'Bu tarih aralığında zaten bir izin kaydı var!']);
            }
        }

        $timeOff->update($validated);

        return back()->with('success', 'İzin güncellendi');
    }

    /**
     * Delete a time off
     */
    public function destroy(StaffTimeOffs $timeOff)
    {
        $user = Auth::user();

        // Check permission
        if ($timeOff->user->salon_id !== $user->salon_id) {
            abort(403);
        }

        $timeOff->delete();

        return back()->with('success', 'İzin silindi');
    }

    /**
     * Get time offs for calendar view (JSON endpoint)
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->salon_id) {
            return response()->json([]);
        }

        $timeOffsQuery = StaffTimeOffs::with('user')
            ->whereHas('user', function ($query) use ($user) {
                $query->where('salon_id', $user->salon_id);
            });

        if ($user->hasRole('staff')) {
            $timeOffsQuery->where('user_id', $user->id);
        }

        $timeOffs = $timeOffsQuery->get()->map(function ($timeOff) {
            return [
                'id' => $timeOff->id,
                'user_id' => $timeOff->user_id,
                'user_name' => $timeOff->user?->name,
                'start' => $timeOff->start_time->format('Y-m-d H:i:s'),
                'end' => $timeOff->end_time->format('Y-m-d H:i:s'),
                'reason' => $timeOff->reason,
            ];
        });

        return response()->json($timeOffs);
    }
}
