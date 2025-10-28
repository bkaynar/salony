<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServicesController extends Controller
{
    /**
     * Display services list
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user || ! $user->salon_id) {
            return Inertia::render('Dashboard/Services/Index', [
                'services' => [],
            ]);
        }

        $services = Service::where('salon_id', $user->salon_id)
            ->orderBy('name')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->price / 100, // Convert to TL
                    'duration_minutes' => $service->duration_minutes,
                    'is_active' => $service->is_active,
                ];
            });

        return Inertia::render('Dashboard/Services/Index', [
            'services' => $services,
        ]);
    }

    /**
     * Store a new service
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Convert TL to kuruş
        $validated['price'] = (int) ($validated['price'] * 100);
        $validated['salon_id'] = $user->salon_id;
        $validated['is_active'] = $validated['is_active'] ?? true;

        Service::create($validated);

        return back()->with('success', 'Hizmet eklendi');
    }

    /**
     * Update a service
     */
    public function update(Request $request, Service $service)
    {
        $user = Auth::user();

        // Check permission
        if ($service->salon_id !== $user->salon_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'duration_minutes' => 'sometimes|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        // Convert TL to kuruş if price is provided
        if (isset($validated['price'])) {
            $validated['price'] = (int) ($validated['price'] * 100);
        }

        $service->update($validated);

        return back()->with('success', 'Hizmet güncellendi');
    }

    /**
     * Delete a service
     */
    public function destroy(Service $service)
    {
        $user = Auth::user();

        // Check permission
        if ($service->salon_id !== $user->salon_id) {
            abort(403);
        }

        $service->delete();

        return back()->with('success', 'Hizmet silindi');
    }
}
