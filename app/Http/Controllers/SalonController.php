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
        // Pass a safe `user` prop: the authenticated user or an empty object.
        // This prevents Vue prop-type validation errors when components expect an object.
        $user = Auth::user() ?? (object)[];

        return Inertia::render('Salons/Show', [
            'salon' => $salon,
            'user' => $user,
        ]);
    }
}
