<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomersController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user || ! $user->salon_id) {
            return Inertia::render('Dashboard/Customers/Index', [
                'customers' => [],
            ]);
        }

        $customers = Customer::where('salon_id', $user->salon_id)
            ->withCount('appointments')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'notes' => $customer->notes,
                    'appointments_count' => $customer->appointments_count,
                    'created_at' => $customer->created_at?->toIso8601String(),
                ];
            });

        return Inertia::render('Dashboard/Customers/Index', [
            'customers' => $customers,
        ]);
    }

    /**
     * Display a customer's details and appointment history
     */
    public function show(Customer $customer)
    {
        $user = Auth::user();

        // Check permission
        if ($customer->salon_id !== $user->salon_id) {
            abort(403);
        }

        // Get customer with appointment history
        $customerData = [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'notes' => $customer->notes,
            'created_at' => $customer->created_at?->toIso8601String(),
        ];

        // Get appointments with related data
        $appointments = $customer->appointments()
            ->with(['staff', 'services.service'])
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'start_time' => $appointment->start_time?->toIso8601String(),
                    'end_time' => $appointment->end_time?->toIso8601String(),
                    'staff_name' => $appointment->staff?->name,
                    'total_price' => $appointment->total_price / 100, // Convert from kuruş to TL
                    'total_duration' => $appointment->total_duration,
                    'status' => $appointment->status,
                    'notes' => $appointment->notes,
                    'booked_by' => $appointment->booked_by,
                    'services' => $appointment->services->map(fn($s) => [
                        'name' => $s->service?->name,
                        'price' => $s->price / 100, // Convert from kuruş to TL
                        'duration_minutes' => $s->duration_minutes,
                    ]),
                ];
            });

        // Calculate statistics
        $totalSpent = $customer->appointments()
            ->where('status', 'completed')
            ->sum('total_price');

        $totalAppointments = $customer->appointments()->count();
        $completedAppointments = $customer->appointments()->where('status', 'completed')->count();
        $cancelledAppointments = $customer->appointments()->where('status', 'cancelled')->count();

        return Inertia::render('Dashboard/Customers/Show', [
            'customer' => $customerData,
            'appointments' => $appointments,
            'stats' => [
                'total_spent' => $totalSpent / 100, // Convert from kuruş to TL
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'cancelled_appointments' => $cancelledAppointments,
            ],
        ]);
    }

    /**
     * Store a new customer
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        Customer::create([
            'salon_id' => $user->salon_id,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Müşteri oluşturuldu');
    }

    /**
     * Update a customer
     */
    public function update(Request $request, Customer $customer)
    {
        $user = Auth::user();

        // Check permission
        if ($customer->salon_id !== $user->salon_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Müşteri güncellendi');
    }

    /**
     * Delete a customer
     */
    public function destroy(Customer $customer)
    {
        $user = Auth::user();

        // Check permission
        if ($customer->salon_id !== $user->salon_id) {
            abort(403);
        }

        $customer->delete();

        return back()->with('success', 'Müşteri silindi');
    }
}
