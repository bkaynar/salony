<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Salons;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Appointments;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $salonAdminRole = Role::firstOrCreate(['name' => 'salon_admin']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);

        // Create a demo salon
        $salon = Salons::firstOrCreate(
            ['subdomain' => 'demo-salon'],
            [
                'name' => 'Demo Güzellik Salonu',
                'phone' => '0532 123 45 67',
                'address' => 'Demo Mahallesi, Demo Sokak No:1, İstanbul',
            ]
        );

        // Create salon admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Salon Sahibi',
                'salon_id' => $salon->id,
                'password' => bcrypt('password'),
                'is_bookable' => false,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($salonAdminRole);

        // Create staff members
        $staff1 = User::firstOrCreate(
            ['email' => 'ayse@demo.com'],
            [
                'name' => 'Ayşe Yılmaz',
                'salon_id' => $salon->id,
                'password' => bcrypt('password'),
                'is_bookable' => true,
                'email_verified_at' => now(),
            ]
        );
        $staff1->assignRole($staffRole);

        $staff2 = User::firstOrCreate(
            ['email' => 'mehmet@demo.com'],
            [
                'name' => 'Mehmet Demir',
                'salon_id' => $salon->id,
                'password' => bcrypt('password'),
                'is_bookable' => true,
                'email_verified_at' => now(),
            ]
        );
        $staff2->assignRole($staffRole);

        // Create customers
        $customers = [
            ['name' => 'Zeynep Kaya', 'phone' => '0532 111 11 11', 'email' => 'zeynep@example.com'],
            ['name' => 'Ahmet Çelik', 'phone' => '0532 222 22 22', 'email' => 'ahmet@example.com'],
            ['name' => 'Fatma Arslan', 'phone' => '0532 333 33 33', 'email' => 'fatma@example.com'],
            ['name' => 'Ali Yıldız', 'phone' => '0532 444 44 44', 'email' => 'ali@example.com'],
        ];

        $createdCustomers = [];
        foreach ($customers as $customerData) {
            $createdCustomers[] = Customer::firstOrCreate(
                ['email' => $customerData['email']],
                array_merge($customerData, ['salon_id' => $salon->id])
            );
        }

        // Create services
        $services = [
            ['name' => 'Saç Kesimi', 'price' => 15000, 'duration_minutes' => 30],
            ['name' => 'Saç Boyama', 'price' => 50000, 'duration_minutes' => 120],
            ['name' => 'Manikür', 'price' => 10000, 'duration_minutes' => 45],
            ['name' => 'Pedikür', 'price' => 12000, 'duration_minutes' => 60],
            ['name' => 'Cilt Bakımı', 'price' => 30000, 'duration_minutes' => 90],
            ['name' => 'Makyaj', 'price' => 25000, 'duration_minutes' => 60],
        ];

        $createdServices = [];
        foreach ($services as $serviceData) {
            $createdServices[] = Service::firstOrCreate(
                [
                    'salon_id' => $salon->id,
                    'name' => $serviceData['name']
                ],
                array_merge($serviceData, ['is_active' => true])
            );
        }

        // Create some appointments
        $appointments = [
            [
                'staff_id' => $staff1->id,
                'customer_id' => $createdCustomers[0]->id,
                'start_time' => now()->addDays(1)->setHour(10)->setMinute(0),
                'services' => [0, 2], // Saç Kesimi + Manikür
            ],
            [
                'staff_id' => $staff1->id,
                'customer_id' => $createdCustomers[1]->id,
                'start_time' => now()->addDays(1)->setHour(14)->setMinute(0),
                'services' => [1], // Saç Boyama
            ],
            [
                'staff_id' => $staff2->id,
                'customer_id' => $createdCustomers[2]->id,
                'start_time' => now()->addDays(2)->setHour(11)->setMinute(0),
                'services' => [4], // Cilt Bakımı
            ],
            [
                'staff_id' => $staff2->id,
                'customer_id' => $createdCustomers[3]->id,
                'start_time' => now()->addDays(2)->setHour(15)->setMinute(0),
                'services' => [2, 3], // Manikür + Pedikür
            ],
        ];

        foreach ($appointments as $apptData) {
            // Calculate totals
            $totalPrice = 0;
            $totalDuration = 0;
            foreach ($apptData['services'] as $serviceIndex) {
                $totalPrice += $createdServices[$serviceIndex]->price;
                $totalDuration += $createdServices[$serviceIndex]->duration_minutes;
            }

            $endTime = (clone $apptData['start_time'])->addMinutes($totalDuration);

            $appointment = Appointments::firstOrCreate(
                [
                    'salon_id' => $salon->id,
                    'staff_id' => $apptData['staff_id'],
                    'customer_id' => $apptData['customer_id'],
                    'start_time' => $apptData['start_time'],
                ],
                [
                    'end_time' => $endTime,
                    'total_price' => $totalPrice,
                    'total_duration' => $totalDuration,
                    'status' => 'confirmed',
                    'booked_by' => 'staff',
                ]
            );

            // Attach services
            foreach ($apptData['services'] as $serviceIndex) {
                $service = $createdServices[$serviceIndex];
                $appointment->services()->firstOrCreate(
                    [
                        'appointment_id' => $appointment->id,
                        'service_id' => $service->id,
                    ],
                    [
                        'price' => $service->price,
                        'duration_minutes' => $service->duration_minutes,
                    ]
                );
            }
        }

        $this->command->info('Demo data created successfully!');
        $this->command->info('Salon: ' . $salon->name);
        $this->command->info('Admin: admin@demo.com / password');
        $this->command->info('Staff 1: ayse@demo.com / password');
        $this->command->info('Staff 2: mehmet@demo.com / password');
    }
}
