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
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.appointments');

Route::post('dashboard/appointments', [\App\Http\Controllers\Dashboard\AppointmentsController::class, 'store'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.appointments.store');

Route::put('dashboard/appointments/{appointment}', [\App\Http\Controllers\Dashboard\AppointmentsController::class, 'update'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.appointments.update');

Route::delete('dashboard/appointments/{appointment}', [\App\Http\Controllers\Dashboard\AppointmentsController::class, 'destroy'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.appointments.destroy');

Route::post('dashboard/appointments/{appointment}/complete-payment', [\App\Http\Controllers\Dashboard\AppointmentsController::class, 'completeWithPayment'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.appointments.complete-payment');

// Customers
Route::get('dashboard/customers', [\App\Http\Controllers\Dashboard\CustomersController::class, 'index'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.customers');

Route::get('dashboard/customers/{customer}', [\App\Http\Controllers\Dashboard\CustomersController::class, 'show'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.customers.show');

Route::post('dashboard/customers', [\App\Http\Controllers\Dashboard\CustomersController::class, 'store'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.customers.store');

Route::put('dashboard/customers/{customer}', [\App\Http\Controllers\Dashboard\CustomersController::class, 'update'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.customers.update');

Route::delete('dashboard/customers/{customer}', [\App\Http\Controllers\Dashboard\CustomersController::class, 'destroy'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.customers.destroy');

// Reports
Route::get('dashboard/reports', [\App\Http\Controllers\Dashboard\ReportsController::class, 'index'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.reports');

// Use subdomain as the route binding key (we store it in `subdomain` column)
Route::get('/{salon:subdomain}', [SalonController::class, 'show'])->name('salons.show');

require __DIR__.'/settings.php';
