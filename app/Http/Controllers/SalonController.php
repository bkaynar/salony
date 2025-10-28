<?php

namespace App\Http\Controllers;

use App\Models\Salons;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SalonController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Salons $salon)
    {
        // Load salon with services, staff, and plan
        $salon->load([
            'services' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            },
            'users' => function ($query) {
                $query->where('is_bookable', true)
                    ->whereHas('roles', fn($q) => $q->whereIn('name', ['salon_admin', 'staff']))
                    ->orderBy('name');
            },
            'plan'
        ]);

        return Inertia::render('Public/Salon/Show', [
            'salon' => [
                'id' => $salon->id,
                'name' => $salon->name,
                'subdomain' => $salon->subdomain,
                'phone' => $salon->phone,
                'address' => $salon->address,
                'settings' => $salon->settings,
                'allow_online_booking' => $salon->plan?->allow_online_booking ?? false,
            ],
            'services' => $salon->services->map(fn($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
                'description' => $service->description ?? null,
            ]),
            'staff' => $salon->users->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
            ]),
        ]);
    }
}
