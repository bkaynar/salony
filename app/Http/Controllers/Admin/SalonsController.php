<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salons;
use App\Models\Plans;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalonsController extends Controller
{
    public function index()
    {
        $salons = Salons::with('plan')
            ->withCount('users')
            ->latest()
            ->get()
            ->map(fn($salon) => [
                'id' => $salon->id,
                'name' => $salon->name,
                'subdomain' => $salon->subdomain,
                'phone' => $salon->phone,
                'address' => $salon->address,
                'plan_id' => $salon->plan_id,
                'plan_name' => $salon->plan?->name,
                'subscription_ends_at' => $salon->subscription_ends_at?->format('Y-m-d'),
                'users_count' => $salon->users_count,
                'created_at' => $salon->created_at->format('d.m.Y H:i'),
                'is_active' => $salon->subscription_ends_at ? $salon->subscription_ends_at->isFuture() : false,
            ]);

        $plans = Plans::all()->map(fn($plan) => [
            'id' => $plan->id,
            'name' => $plan->name,
        ]);

        return Inertia::render('Admin/Salons/Index', [
            'salons' => $salons,
            'plans' => $plans,
        ]);
    }

    public function update(Request $request, Salons $salon)
    {
        $validated = $request->validate([
            'plan_id' => 'sometimes|nullable|exists:plans,id',
            'subscription_ends_at' => 'sometimes|nullable|date',
        ]);

        $salon->update($validated);

        return back()->with('success', 'Salon güncellendi');
    }

    public function destroy(Salons $salon)
    {
        $salon->delete();

        return back()->with('success', 'Salon silindi');
    }
}
