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

// Typeahead search endpoints for appointments UI
Route::get('dashboard/appointments/search/staff', [\App\Http\Controllers\Dashboard\AppointmentsController::class, 'searchStaff'])
    ->middleware([ 'auth', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff', ])
    ->name('dashboard.appointments.search.staff');

Route::get('dashboard/appointments/search/customers', [\App\Http\Controllers\Dashboard\AppointmentsController::class, 'searchCustomers'])
    ->middleware([ 'auth', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff', ])
    ->name('dashboard.appointments.search.customers');

Route::get('dashboard/appointments/upcoming', [\App\Http\Controllers\Dashboard\AppointmentsController::class, 'getUpcomingAppointments'])
    ->middleware([ 'auth', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff', ])
    ->name('dashboard.appointments.upcoming');

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

// Report Exports
Route::get('dashboard/reports/export/pdf', [\App\Http\Controllers\Dashboard\ReportsController::class, 'exportPdf'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.reports.export.pdf');

Route::get('dashboard/reports/export/excel', [\App\Http\Controllers\Dashboard\ReportsController::class, 'exportExcel'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.reports.export.excel');

// Expense Management
Route::post('dashboard/expenses', [\App\Http\Controllers\Dashboard\ReportsController::class, 'storeExpense'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.expenses.store');

Route::put('dashboard/expenses/{expense}', [\App\Http\Controllers\Dashboard\ReportsController::class, 'updateExpense'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.expenses.update');

Route::delete('dashboard/expenses/{expense}', [\App\Http\Controllers\Dashboard\ReportsController::class, 'destroyExpense'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.expenses.destroy');

// Staff Management
Route::get('dashboard/staff', [\App\Http\Controllers\Dashboard\StaffController::class, 'index'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.staff');

Route::post('dashboard/staff', [\App\Http\Controllers\Dashboard\StaffController::class, 'store'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.staff.store');

Route::put('dashboard/staff/{staff}', [\App\Http\Controllers\Dashboard\StaffController::class, 'update'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.staff.update');

Route::delete('dashboard/staff/{staff}', [\App\Http\Controllers\Dashboard\StaffController::class, 'destroy'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.staff.destroy');

// Services (Hizmetler)
Route::get('dashboard/services', [\App\Http\Controllers\Dashboard\ServicesController::class, 'index'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.services');

Route::post('dashboard/services', [\App\Http\Controllers\Dashboard\ServicesController::class, 'store'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.services.store');

Route::put('dashboard/services/{service}', [\App\Http\Controllers\Dashboard\ServicesController::class, 'update'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.services.update');

Route::delete('dashboard/services/{service}', [\App\Http\Controllers\Dashboard\ServicesController::class, 'destroy'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin',
    ])
    ->name('dashboard.services.destroy');

// Staff Working Hours (Çalışma Saatleri)
Route::get('dashboard/staff-working', [\App\Http\Controllers\Dashboard\StaffWorkingController::class, 'index'])
    ->middleware(['auth', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff'])
    ->name('dashboard.staff-working');

Route::post('dashboard/staff-working', [\App\Http\Controllers\Dashboard\StaffWorkingController::class, 'store'])
    ->middleware(['auth', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin'])
    ->name('dashboard.staff-working.store');

Route::post('dashboard/staff-working/bulk', [\App\Http\Controllers\Dashboard\StaffWorkingController::class, 'bulkUpdate'])
    ->middleware(['auth', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin'])
    ->name('dashboard.staff-working.bulk');

Route::put('dashboard/staff-working/{staffWorking}', [\App\Http\Controllers\Dashboard\StaffWorkingController::class, 'update'])
    ->middleware(['auth', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin'])
    ->name('dashboard.staff-working.update');

Route::delete('dashboard/staff-working/{staffWorking}', [\App\Http\Controllers\Dashboard\StaffWorkingController::class, 'destroy'])
    ->middleware(['auth', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin'])
    ->name('dashboard.staff-working.destroy');

// Staff Time Offs (Personel İzinleri)
Route::get('dashboard/time-offs', [\App\Http\Controllers\Dashboard\StaffTimeOffsController::class, 'index'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.time-offs');

Route::get('dashboard/time-offs/calendar', [\App\Http\Controllers\Dashboard\StaffTimeOffsController::class, 'calendar'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.time-offs.calendar');

Route::post('dashboard/time-offs', [\App\Http\Controllers\Dashboard\StaffTimeOffsController::class, 'store'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.time-offs.store');

Route::put('dashboard/time-offs/{timeOff}', [\App\Http\Controllers\Dashboard\StaffTimeOffsController::class, 'update'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.time-offs.update');

Route::delete('dashboard/time-offs/{timeOff}', [\App\Http\Controllers\Dashboard\StaffTimeOffsController::class, 'destroy'])
    ->middleware([
        'auth',
        'verified',
        \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin|staff',
    ])
    ->name('dashboard.time-offs.destroy');

// Products (Ürünler)
Route::resource('dashboard/products', \App\Http\Controllers\Dashboard\ProductsController::class)
    ->middleware(['auth', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':salon_admin'])
    ->only(['index', 'store', 'update', 'destroy']);

// Admin Panel Routes (Super Admin Only)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('plans', \App\Http\Controllers\Admin\PlansController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('salons', \App\Http\Controllers\Admin\SalonsController::class)->only(['index', 'update', 'destroy']);
});

// Use subdomain as the route binding key (we store it in `subdomain` column)
Route::get('/{salon:subdomain}', [SalonController::class, 'show'])->name('salons.show');

require __DIR__.'/settings.php';
