<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salons;
use App\Models\User;
use App\Models\Plans;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSalons = Salons::count();
        $activeSalons = Salons::whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '>', now())
            ->count();
        $totalUsers = User::count();
        $totalRevenue = Salons::whereNotNull('plan_id')
            ->join('plans', 'salons.plan_id', '=', 'plans.id')
            ->sum('plans.price_monthly');

        // Recent salons
        $recentSalons = Salons::with('plan')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($salon) => [
                'id' => $salon->id,
                'name' => $salon->name,
                'subdomain' => $salon->subdomain,
                'plan_name' => $salon->plan?->name,
                'subscription_ends_at' => $salon->subscription_ends_at?->format('d.m.Y'),
                'created_at' => $salon->created_at->format('d.m.Y'),
            ]);

        // Plan statistics
        $planStats = Plans::withCount('salons')
            ->get()
            ->map(fn($plan) => [
                'name' => $plan->name,
                'count' => $plan->salons_count,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalSalons' => $totalSalons,
                'activeSalons' => $activeSalons,
                'totalUsers' => $totalUsers,
                'totalRevenue' => $totalRevenue,
            ],
            'recentSalons' => $recentSalons,
            'planStats' => $planStats,
        ]);
    }
}
