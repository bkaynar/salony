<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plans;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlansController extends Controller
{
    public function index()
    {
        $plans = Plans::withCount('salons')
            ->orderBy('price_monthly')
            ->get()
            ->map(fn($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price_monthly' => $plan->price_monthly,
                'price_yearly' => $plan->price_yearly,
                'max_staff_count' => $plan->max_staff_count,
                'allow_online_booking' => $plan->allow_online_booking,
                'allow_sms_notifications' => $plan->allow_sms_notifications,
                'salons_count' => $plan->salons_count,
            ]);

        return Inertia::render('Admin/Plans/Index', [
            'plans' => $plans,
        ]);
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

        return back()->with('success', 'Plan oluşturuldu');
    }

    public function update(Request $request, Plans $plan)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price_monthly' => 'sometimes|numeric|min:0',
            'price_yearly' => 'sometimes|numeric|min:0',
            'max_staff_count' => 'sometimes|integer|min:1',
            'allow_online_booking' => 'boolean',
            'allow_sms_notifications' => 'boolean',
        ]);

        $plan->update($validated);

        return back()->with('success', 'Plan güncellendi');
    }

    public function destroy(Plans $plan)
    {
        if ($plan->salons()->count() > 0) {
            return back()->withErrors(['error' => 'Bu plana bağlı salonlar var, önce onları değiştirin']);
        }

        $plan->delete();

        return back()->with('success', 'Plan silindi');
    }
}
