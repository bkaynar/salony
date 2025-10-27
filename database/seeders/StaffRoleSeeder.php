<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StaffRoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'appointments.view',
            'appointments.create',
            'appointments.update',
            // intentionally not giving delete by default
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        // Give view and update permissions to staff by default
        $role->givePermissionTo(['appointments.view', 'appointments.update']);

        $this->command->info("Created role 'staff' and appointment permissions (view/update)");
    }
}
