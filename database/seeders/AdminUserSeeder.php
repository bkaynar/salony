<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Salons;
use App\Models\Plans;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $salonAdminRole = Role::firstOrCreate(['name' => 'salon_admin']);

        // Create super admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@salony.com'],
            [
                'name' => 'Super Admin',
                'salon_id' => null, // Admin is not tied to any salon
                'password' => bcrypt('admin123'),
                'is_bookable' => false,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($adminRole);

        // Create demo subscription plans
        $basicPlan = Plans::firstOrCreate(
            ['name' => 'Basic'],
            [
                'price_monthly' => 29900, // 299 TL
                'price_yearly' => 299900, // 2999 TL
                'max_staff_count' => 3,
                'allow_online_booking' => true,
                'allow_sms_notifications' => false,
            ]
        );

        $proPlan = Plans::firstOrCreate(
            ['name' => 'Pro'],
            [
                'price_monthly' => 49900, // 499 TL
                'price_yearly' => 499900, // 4999 TL
                'max_staff_count' => 10,
                'allow_online_booking' => true,
                'allow_sms_notifications' => true,
            ]
        );

        $premiumPlan = Plans::firstOrCreate(
            ['name' => 'Premium'],
            [
                'price_monthly' => 79900, // 799 TL
                'price_yearly' => 799900, // 7999 TL
                'max_staff_count' => 999,
                'allow_online_booking' => true,
                'allow_sms_notifications' => true,
            ]
        );

        // Create demo salons with different plans
        $salon1 = Salons::firstOrCreate(
            ['subdomain' => 'guzellik-dunyasi'],
            [
                'name' => 'Güzellik Dünyası',
                'phone' => '0532 100 20 30',
                'address' => 'Kadıköy, İstanbul',
                'plan_id' => $proPlan->id,
                'subscription_ends_at' => now()->addMonths(3),
            ]
        );

        $salon2 = Salons::firstOrCreate(
            ['subdomain' => 'elit-salon'],
            [
                'name' => 'Elit Kuaför Salonu',
                'phone' => '0532 200 30 40',
                'address' => 'Beşiktaş, İstanbul',
                'plan_id' => $basicPlan->id,
                'subscription_ends_at' => now()->addMonths(1),
            ]
        );

        $salon3 = Salons::firstOrCreate(
            ['subdomain' => 'vip-beauty'],
            [
                'name' => 'VIP Beauty Center',
                'phone' => '0532 300 40 50',
                'address' => 'Nişantaşı, İstanbul',
                'plan_id' => $premiumPlan->id,
                'subscription_ends_at' => now()->addYear(),
            ]
        );

        // Create a salon admin for each demo salon
        $salonOwner1 = User::firstOrCreate(
            ['email' => 'owner1@salony.com'],
            [
                'name' => 'Salon Sahibi 1',
                'salon_id' => $salon1->id,
                'password' => bcrypt('password'),
                'is_bookable' => false,
                'email_verified_at' => now(),
            ]
        );
        $salonOwner1->assignRole($salonAdminRole);

        $salonOwner2 = User::firstOrCreate(
            ['email' => 'owner2@salony.com'],
            [
                'name' => 'Salon Sahibi 2',
                'salon_id' => $salon2->id,
                'password' => bcrypt('password'),
                'is_bookable' => false,
                'email_verified_at' => now(),
            ]
        );
        $salonOwner2->assignRole($salonAdminRole);

        $salonOwner3 = User::firstOrCreate(
            ['email' => 'owner3@salony.com'],
            [
                'name' => 'Salon Sahibi 3',
                'salon_id' => $salon3->id,
                'password' => bcrypt('password'),
                'is_bookable' => false,
                'email_verified_at' => now(),
            ]
        );
        $salonOwner3->assignRole($salonAdminRole);

        $this->command->info('Admin user and demo data created successfully!');
        $this->command->info('');
        $this->command->info('=== ADMIN ACCESS ===');
        $this->command->info('Email: admin@salony.com');
        $this->command->info('Password: admin123');
        $this->command->info('');
        $this->command->info('=== DEMO SALONS ===');
        $this->command->info('1. ' . $salon1->name . ' - owner1@salony.com / password');
        $this->command->info('2. ' . $salon2->name . ' - owner2@salony.com / password');
        $this->command->info('3. ' . $salon3->name . ' - owner3@salony.com / password');
        $this->command->info('');
        $this->command->info('Plans created: Basic, Pro, Premium');
    }
}
