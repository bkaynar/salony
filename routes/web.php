<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Controllers
use App\Http\Controllers\SalonController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Salon admin: appointments page
Route::get('dashboard/appointments', [\App\Http\Controllers\Dashboard\AppointmentsController::class, 'index'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.appointments');

Route::get('dashboard/appointments/calendar', [\App\Http\Controllers\Dashboard\AppointmentsController::class, 'calendar'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.appointments.calendar');

// Use subdomain as the route binding key (we store it in `subdomain` column)
Route::get('/{salon:subdomain}', [SalonController::class, 'show'])->name('salons.show');

require __DIR__.'/settings.php';
