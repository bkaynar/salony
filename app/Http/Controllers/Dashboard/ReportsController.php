<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payments;
use App\Models\Appointments;
use App\Models\Expenses;
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
                'expenses' => [],
                'expensesByCategory' => [],
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

        // Get expenses for the period
        $expenses = Expenses::where('salon_id', $user->salon_id)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'category' => $expense->category,
                    'amount' => $expense->amount / 100, // Convert to TL
                    'description' => $expense->description,
                    'expense_date' => $expense->expense_date->format('Y-m-d'),
                ];
            });

        // Total expenses
        $totalExpenses = Expenses::where('salon_id', $user->salon_id)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        // Expenses by category
        $expensesByCategory = Expenses::where('salon_id', $user->salon_id)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'total' => $item->total / 100, // Convert to TL
                    'count' => $item->count,
                ];
            });

        // Calculate net income (revenue - expenses)
        $netIncome = $totalRevenue - $totalExpenses;

        return Inertia::render('Dashboard/Reports/Index', [
            'stats' => [
                'total_revenue' => $totalRevenue / 100, // Convert to TL
                'total_expenses' => $totalExpenses / 100, // Convert to TL
                'net_income' => $netIncome / 100, // Convert to TL
                'total_appointments' => $totalAppointments,
                'avg_transaction' => $avgTransaction / 100, // Convert to TL
            ],
            'dailyRevenue' => $dailyRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'paymentMethods' => $paymentMethods,
            'expenses' => $expenses,
            'expensesByCategory' => $expensesByCategory,
            'dateRange' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Store a new expense
     */
    public function storeExpense(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'category' => 'required|in:personel,kira,fatura,diger',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'expense_date' => 'required|date',
        ]);

        // Convert TL to kuruş for storage
        $amountInCents = (int) ($validated['amount'] * 100);

        Expenses::create([
            'salon_id' => $user->salon_id,
            'category' => $validated['category'],
            'amount' => $amountInCents,
            'description' => $validated['description'] ?? null,
            'expense_date' => $validated['expense_date'],
        ]);

        return back()->with('success', 'Gider eklendi');
    }

    /**
     * Update an expense
     */
    public function updateExpense(Request $request, Expenses $expense)
    {
        $user = Auth::user();

        // Check permission
        if ($expense->salon_id !== $user->salon_id) {
            abort(403);
        }

        $validated = $request->validate([
            'category' => 'sometimes|in:personel,kira,fatura,diger',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'expense_date' => 'sometimes|date',
        ]);

        // Convert TL to kuruş if amount is provided
        if (isset($validated['amount'])) {
            $validated['amount'] = (int) ($validated['amount'] * 100);
        }

        $expense->update($validated);

        return back()->with('success', 'Gider güncellendi');
    }

    /**
     * Delete an expense
     */
    public function destroyExpense(Expenses $expense)
    {
        $user = Auth::user();

        // Check permission
        if ($expense->salon_id !== $user->salon_id) {
            abort(403);
        }

        $expense->delete();

        return back()->with('success', 'Gider silindi');
    }
}
