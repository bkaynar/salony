<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payments;
use App\Models\Appointments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display financial reports
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->salon_id) {
            return Inertia::render('Dashboard/Reports/Index', [
                'stats' => [],
                'dailyRevenue' => [],
                'monthlyRevenue' => [],
                'paymentMethods' => [],
            ]);
        }

        // Get date range from request or default to current month
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfMonth();

        // Total revenue for the period
        $totalRevenue = Payments::where('salon_id', $user->salon_id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // Total completed appointments
        $totalAppointments = Appointments::where('salon_id', $user->salon_id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Average transaction value
        $avgTransaction = $totalAppointments > 0 ? $totalRevenue / $totalAppointments : 0;

        // Payment methods breakdown
        $paymentMethods = Payments::where('salon_id', $user->salon_id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->method,
                    'total' => $item->total / 100, // Convert to TL
                    'count' => $item->count,
                ];
            });

        // Daily revenue for chart
        $dailyRevenue = Payments::where('salon_id', $user->salon_id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total' => $item->total / 100, // Convert to TL
                    'count' => $item->count,
                ];
            });

        // Monthly comparison (last 6 months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $revenue = Payments::where('salon_id', $user->salon_id)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');

            $appointments = Appointments::where('salon_id', $user->salon_id)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $monthlyRevenue[] = [
                'month' => $month->format('M Y'),
                'revenue' => $revenue / 100, // Convert to TL
                'appointments' => $appointments,
            ];
        }

        return Inertia::render('Dashboard/Reports/Index', [
            'stats' => [
                'total_revenue' => $totalRevenue / 100, // Convert to TL
                'total_appointments' => $totalAppointments,
                'avg_transaction' => $avgTransaction / 100, // Convert to TL
            ],
            'dailyRevenue' => $dailyRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'paymentMethods' => $paymentMethods,
            'dateRange' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ]);
    }
}
