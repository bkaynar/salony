<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointments;
use App\Models\User;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Payments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentsController extends Controller
{
    /**
     * Display appointments organized by staff with calendar view
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user || ! $user->salon_id) {
            return Inertia::render('Dashboard/Appointments/Index', [
                'staffList' => [],
                'customers' => [],
                'services' => [],
            ]);
        }

        // Get all staff members for this salon
        $staffQuery = User::where('salon_id', $user->salon_id)
            ->where('is_bookable', true);

        if ($user->hasRole('staff')) {
            // If user is staff, only show their own calendar
            $staffQuery->where('id', $user->id);
        }

        $staffList = $staffQuery->get()->map(function ($staff) use ($user) {
            // Get appointments for each staff member
            $appointments = Appointments::with(['customer', 'services.service'])
                ->where('salon_id', $user->salon_id)
                ->where('staff_id', $staff->id)
                ->orderBy('start_time', 'asc')
                ->get()
                ->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'title' => $appointment->customer?->name ?? 'Müşteri',
                        'start' => $appointment->start_time, // Already a string, no need to convert
                        'end' => $appointment->end_time,     // Already a string, no need to convert
                        'customer_id' => $appointment->customer_id,
                        'customer_name' => $appointment->customer?->name,
                        'customer_phone' => $appointment->customer?->phone,
                        'staff_id' => $appointment->staff_id,
                        'total_price' => $appointment->total_price / 100, // Convert from kuruş to TL
                        'total_duration' => $appointment->total_duration,
                        'status' => $appointment->status,
                        'notes' => $appointment->notes,
                        'services' => $appointment->services->map(fn($s) => [
                            'service_id' => $s->service_id,
                            'name' => $s->service?->name,
                            'price' => $s->price / 100, // Convert from kuruş to TL
                            'duration_minutes' => $s->duration_minutes,
                        ]),
                    ];
                });

            return [
                'id' => $staff->id,
                'name' => $staff->name,
                'appointments' => $appointments,
            ];
        });

        // Get customers and services for dropdowns
        $customers = Customer::where('salon_id', $user->salon_id)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email']);

        $services = Service::where('salon_id', $user->salon_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'duration_minutes'])
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price / 100, // Convert from kuruş to TL
                    'duration_minutes' => $service->duration_minutes,
                ];
            });

        return Inertia::render('Dashboard/Appointments/Index', [
            'staffList' => $staffList,
            'customers' => $customers,
            'services' => $services,
        ]);
    }

    /**
     * Server-side search for staff (typeahead)
     */
    public function searchStaff(Request $request)
    {
        $user = Auth::user();
        $q = trim($request->get('q', ''));

        if (! $user || ! $user->salon_id || $q === '') {
            return response()->json([]);
        }

        $results = User::where('salon_id', $user->salon_id)
            ->where('is_bookable', true)
            ->where(function ($qbuilder) use ($q) {
                $qbuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'email'])
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                ];
            });

        return response()->json($results);
    }

    /**
     * Server-side search for customers (typeahead)
     */
    public function searchCustomers(Request $request)
    {
        $user = Auth::user();
        $q = trim($request->get('q', ''));

        if (! $user || ! $user->salon_id || $q === '') {
            return response()->json([]);
        }

        $results = \App\Models\Customer::where('salon_id', $user->salon_id)
            ->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'phone', 'email'])
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'phone' => $c->phone,
                    'email' => $c->email,
                ];
            });

        return response()->json($results);
    }

    /**
     * Store a new appointment
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'staff_id' => 'required|exists:users,id',
            'customer_id' => 'required|exists:customers,id',
            'start_time' => 'required|string', // Changed from 'date' to prevent timezone conversion
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'notes' => 'nullable|string',
        ]);

        // Calculate totals from services
        $totalPrice = 0;
        $totalDuration = 0;

        $servicesList = Service::whereIn('id', collect($validated['services'])->pluck('service_id'))->get();

        foreach ($servicesList as $service) {
            $totalPrice += $service->price;
            $totalDuration += $service->duration_minutes;
        }

        // Calculate end time
        $startTime = new \DateTime($validated['start_time']);
        $endTime = (clone $startTime)->modify("+{$totalDuration} minutes");

        // Create appointment
        $appointment = Appointments::create([
            'salon_id' => $user->salon_id,
            'customer_id' => $validated['customer_id'],
            'staff_id' => $validated['staff_id'],
            'start_time' => $startTime->format('Y-m-d H:i:s'), // Store as string to avoid timezone conversion
            'end_time' => $endTime->format('Y-m-d H:i:s'),     // Store as string to avoid timezone conversion
            'total_price' => $totalPrice,
            'total_duration' => $totalDuration,
            'status' => 'confirmed',
            'notes' => $validated['notes'] ?? null,
            'booked_by' => 'staff',
        ]);

        // Attach services
        foreach ($servicesList as $service) {
            $appointment->services()->create([
                'service_id' => $service->id,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
            ]);
        }

        return back()->with('success', 'Randevu oluşturuldu');
    }

    /**
     * Update an appointment
     */
    public function update(Request $request, Appointments $appointment)
    {
        $user = Auth::user();

        // Check permission
        if ($appointment->salon_id !== $user->salon_id) {
            abort(403);
        }

        $validated = $request->validate([
            'staff_id' => 'sometimes|exists:users,id',
            'customer_id' => 'sometimes|exists:customers,id',
            'start_time' => 'sometimes|string', // Changed from 'date' to prevent timezone conversion
            'end_time' => 'sometimes|string', // Allow manual end_time update (for resize)
            'services' => 'sometimes|array|min:1',
            'services.*.service_id' => 'required_with:services|exists:services,id',
            'status' => 'sometimes|in:confirmed,completed,cancelled,no_show',
            'notes' => 'nullable|string',
        ]);

        // Handle payment status when appointment status changes
        if (isset($validated['status'])) {
            $oldStatus = $appointment->status;
            $newStatus = $validated['status'];

            // If changing FROM completed TO another status, delete the payment record
            if ($oldStatus === 'completed' && $newStatus !== 'completed') {
                Payments::where('appointment_id', $appointment->id)
                    ->where('salon_id', $user->salon_id)
                    ->delete();
            }
        }

        // If services changed, recalculate totals
        if (isset($validated['services']) && is_array($validated['services']) && count($validated['services']) > 0) {
            $totalPrice = 0;
            $totalDuration = 0;

            $servicesList = Service::whereIn('id', collect($validated['services'])->pluck('service_id'))->get();

            foreach ($servicesList as $service) {
                $totalPrice += $service->price;
                $totalDuration += $service->duration_minutes;
            }

            $validated['total_price'] = $totalPrice;
            $validated['total_duration'] = $totalDuration;

            // Recalculate end time if start time exists
            $startTime = new \DateTime($validated['start_time'] ?? $appointment->start_time);
            $endTime = (clone $startTime)->modify("+{$totalDuration} minutes");
            $validated['end_time'] = $endTime->format('Y-m-d H:i:s'); // Store as string to avoid timezone conversion

            // Only update services if they actually changed
            // Delete old services and create new ones
            $appointment->services()->delete();
            foreach ($servicesList as $service) {
                $appointment->services()->create([
                    'service_id' => $service->id,
                    'price' => $service->price,
                    'duration_minutes' => $service->duration_minutes,
                ]);
            }

            unset($validated['services']);
        } elseif (isset($validated['start_time']) && !isset($validated['end_time'])) {
            // If only start_time changed (drag without resize), recalculate end_time
            $startTime = new \DateTime($validated['start_time']);
            $duration = $appointment->total_duration;
            $endTime = (clone $startTime)->modify("+{$duration} minutes");
            $validated['end_time'] = $endTime->format('Y-m-d H:i:s');
        } elseif (isset($validated['end_time']) && isset($validated['start_time'])) {
            // If both start and end time changed (resize), recalculate duration
            $startTime = new \DateTime($validated['start_time']);
            $endTime = new \DateTime($validated['end_time']);
            $diff = $startTime->diff($endTime);
            $newDuration = ($diff->h * 60) + $diff->i;
            $validated['total_duration'] = $newDuration;
        }

        $appointment->update($validated);

        return back()->with('success', 'Randevu güncellendi');
    }

    /**
     * Delete an appointment
     */
    public function destroy(Appointments $appointment)
    {
        $user = Auth::user();

        // Check permission
        if ($appointment->salon_id !== $user->salon_id) {
            abort(403);
        }

        $appointment->delete();

        return back()->with('success', 'Randevu silindi');
    }

    /**
     * Complete appointment and record payment
     */
    public function completeWithPayment(Request $request, Appointments $appointment)
    {
        $user = Auth::user();

        // Check permission
        if ($appointment->salon_id !== $user->salon_id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,credit_card,debit_card,online_payment',
            'notes' => 'nullable|string',
        ]);

        // Convert TL to kuruş for storage
        $amountInCents = (int) ($validated['amount_paid'] * 100);

        // Update appointment status
        $appointment->update([
            'status' => 'completed',
            'notes' => $validated['notes'] ?? $appointment->notes,
        ]);

        // Create payment record
        Payments::create([
            'salon_id' => $user->salon_id,
            'appointment_id' => $appointment->id,
            'customer_id' => $appointment->customer_id,
            'amount' => $amountInCents,
            'method' => $validated['payment_method'],
            'status' => 'completed',
        ]);

        return back()->with('success', 'Randevu tamamlandı ve ödeme kaydedildi');
    }

    /**
     * Get upcoming appointments for notifications
     */
    public function getUpcomingAppointments(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->salon_id) {
            return response()->json([]);
        }

        $daysAhead = (int) $request->input('days', 2); // Default 2 days ahead - CAST TO INT!

        // If days = 0, show only today (from now onwards, not past appointments)
        // If days = 1, show only tomorrow
        // If days > 1, show from tomorrow to that many days ahead (exclude today)
        if ($daysAhead === 0) {
            // Only today - from current time onwards
            $startDatetime = now()->format('Y-m-d H:i:s');
            $endDatetime = now()->endOfDay()->format('Y-m-d H:i:s');

            $appointments = Appointments::with(['customer', 'staff', 'services.service'])
                ->where('salon_id', $user->salon_id)
                ->where('status', 'confirmed')
                ->whereRaw("REPLACE(start_time, 'T', ' ') BETWEEN ? AND ?", [
                    $startDatetime,
                    $endDatetime
                ])
                ->orderBy('start_time', 'asc')
                ->get();
        } elseif ($daysAhead === 1) {
            // Only tomorrow
            $startDate = now()->addDay()->startOfDay()->format('Y-m-d');
            $endDate = now()->addDay()->endOfDay()->format('Y-m-d');

            $appointments = Appointments::with(['customer', 'staff', 'services.service'])
                ->where('salon_id', $user->salon_id)
                ->where('status', 'confirmed')
                ->whereRaw("DATE(REPLACE(start_time, 'T', ' ')) BETWEEN ? AND ?", [
                    $startDate,
                    $endDate
                ])
                ->orderBy('start_time', 'asc')
                ->get();
        } else {
            // From tomorrow to X days ahead (exclude today's appointments)
            $startDate = now()->addDay()->startOfDay()->format('Y-m-d');
            $endDate = now()->addDays($daysAhead)->format('Y-m-d');

            $appointments = Appointments::with(['customer', 'staff', 'services.service'])
                ->where('salon_id', $user->salon_id)
                ->where('status', 'confirmed')
                ->whereRaw("DATE(REPLACE(start_time, 'T', ' ')) BETWEEN ? AND ?", [
                    $startDate,
                    $endDate
                ])
                ->orderBy('start_time', 'asc')
                ->get();
        }

        $appointments = $appointments->map(function ($appointment) {
                // Normalize date format (handle both 'T' separator and space separator)
                $normalizedDate = str_replace('T', ' ', $appointment->start_time);
                $startTime = new \DateTime($normalizedDate);
                $now = new \DateTime();

                // Calculate days remaining properly - compare dates only, not times
                $startDate = new \DateTime($startTime->format('Y-m-d'));
                $todayDate = new \DateTime($now->format('Y-m-d'));
                $diff = $todayDate->diff($startDate);

                $daysRemaining = 0;
                if ($startDate >= $todayDate) {
                    $daysRemaining = (int) $diff->format('%a');
                }

                return [
                    'id' => $appointment->id,
                    'customer_name' => $appointment->customer?->name,
                    'customer_phone' => $appointment->customer?->phone,
                    'staff_name' => $appointment->staff?->name,
                    'start_time' => $appointment->start_time,
                    'start_time_formatted' => $startTime->format('d.m.Y H:i'),
                    'days_remaining' => $daysRemaining,
                    'hours_remaining' => (int) $diff->format('%h'),
                    'services' => $appointment->services->map(fn($s) => $s->service?->name)->join(', '),
                ];
            });

        return response()->json($appointments);
    }
}
