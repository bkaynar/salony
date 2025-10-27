<?php

namespace Database\Seeders;

use App\Models\Plans;
use App\Models\Salons;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SalonTestSeeder extends Seeder
{
    public function run(): void
    {
        // Create or fetch a default plan
        $plan = Plans::first() ?: Plans::create([
            'name' => 'Test Plan',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'max_staff_count' => 5,
            'allow_online_booking' => true,
            'allow_sms_notifications' => false,
        ]);

        // Create a salon
        $salon = Salons::create([
            'name' => 'Test Salon',
            'subdomain' => 'test-salon',
            'phone' => '+90 555 000 0000',
            'address' => 'Test address',
            'settings' => [
                'currency' => 'TRY',
                'working_hours' => [],
            ],
            'plan_id' => $plan->id,
        ]);

        // Create permissions and role
        $permission = Permission::firstOrCreate(['name' => 'manage salon', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'salon_admin', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        // Create a managing user for the salon
        $user = User::create([
            'salon_id' => $salon->id,
            'name' => 'Salon Admin',
            'email' => 'admin@test-salon.local',
            'password' => Hash::make('password'),
            'phone' => '+90 555 000 0001',
            'is_bookable' => false,
        ]);

        // Assign role & permission
        $user->assignRole($role);

        // Also make sure user has direct permission (optional)
        $user->givePermissionTo($permission);

        $this->command->info("Created salon '{$salon->name}' (id={$salon->id}) and admin user {$user->email}");
    }
}
