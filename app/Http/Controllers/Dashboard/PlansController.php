<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Plans;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlansController extends Controller
{
    public function index()
    {
        $plans = Plans::orderBy('price_monthly')->get();
        return Inertia::render('Dashboard/Plans/Index', ['plans' => $plans]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'max_staff_count' => 'required|integer|min:1',
            'allow_online_booking' => 'boolean',
            'allow_sms_notifications' => 'boolean',
        ]);

        Plans::create($validated);
        return back()->with('success', 'Plan eklendi');
    }

    public function update(Request $request, Plans $plan)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price_monthly' => 'sometimes|numeric|min:0',
            'price_yearly' => 'sometimes|numeric|min:0',
            'max_staff_count' => 'sometimes|integer|min:1',
            'allow_online_booking' => 'sometimes|boolean',
            'allow_sms_notifications' => 'sometimes|boolean',
        ]);

        $plan->update($validated);
        return back()->with('success', 'Plan güncellendi');
    }

    public function destroy(Plans $plan)
    {
        $plan->delete();
        return back()->with('success', 'Plan silindi');
    }
}
