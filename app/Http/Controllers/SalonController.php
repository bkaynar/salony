<?php

namespace App\Http\Controllers;

use App\Models\Salons;
use Inertia\Inertia;

class SalonController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Salons $salon)
    {
        return Inertia::render('Salons/Show', [
            'salon' => $salon,
        ]);
    }
}