<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    /**
     * Display staff list
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user || ! $user->salon_id) {
            return Inertia::render('Dashboard/Staff/Index', [
                'staff' => [],
            ]);
        }

        $staff = User::where('salon_id', $user->salon_id)
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($staffMember) {
                return [
                    'id' => $staffMember->id,
                    'name' => $staffMember->name,
                    'email' => $staffMember->email,
                    'is_bookable' => $staffMember->is_bookable,
                    'roles' => $staffMember->roles->pluck('name')->toArray(),
                    'created_at' => $staffMember->created_at?->toIso8601String(),
                ];
            });

        return Inertia::render('Dashboard/Staff/Index', [
            'staff' => $staff,
        ]);
    }

    /**
     * Store a new staff member
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'is_bookable' => 'boolean',
            'role' => 'required|in:salon_admin,staff',
        ]);

        $newStaff = User::create([
            'salon_id' => $user->salon_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_bookable' => $validated['is_bookable'] ?? true,
        ]);

        // Assign role
        $newStaff->assignRole($validated['role']);

        return back()->with('success', 'Personel eklendi');
    }

    /**
     * Update staff member
     */
    public function update(Request $request, User $staff)
    {
        $user = Auth::user();

        // Check permission
        if ($staff->salon_id !== $user->salon_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $staff->id,
            'password' => 'nullable|string|min:8',
            'is_bookable' => 'boolean',
            'role' => 'sometimes|required|in:salon_admin,staff',
        ]);

        $updateData = [
            'name' => $validated['name'] ?? $staff->name,
            'email' => $validated['email'] ?? $staff->email,
            'is_bookable' => $validated['is_bookable'] ?? $staff->is_bookable,
        ];

        // Only update password if provided
        if (isset($validated['password']) && !empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $staff->update($updateData);

        // Update role if changed
        if (isset($validated['role'])) {
            $staff->syncRoles([$validated['role']]);
        }

        return back()->with('success', 'Personel güncellendi');
    }

    /**
     * Delete staff member
     */
    public function destroy(User $staff)
    {
        $user = Auth::user();

        // Check permission
        if ($staff->salon_id !== $user->salon_id) {
            abort(403);
        }

        // Prevent deleting yourself
        if ($staff->id === $user->id) {
            return back()->withErrors(['error' => 'Kendi hesabınızı silemezsiniz']);
        }

        $staff->delete();

        return back()->with('success', 'Personel silindi');
    }
}
