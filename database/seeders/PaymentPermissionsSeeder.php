<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PaymentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos para el módulo de pagos
        $paymentPermissions = [
            [
                'name' => 'payments.view',
                'display_name' => 'View Payments',
                'description' => 'Can view payment dashboard and reports',
                'module' => 'payments',
            ],
            [
                'name' => 'payment-reports.view',
                'display_name' => 'View Payment Reports',
                'description' => 'Can view payment reports',
                'module' => 'payments',
            ],
            [
                'name' => 'payment-reports.generate',
                'display_name' => 'Generate Payment Reports',
                'description' => 'Can generate payment reports',
                'module' => 'payments',
            ],
            [
                'name' => 'payment-reports.approve',
                'display_name' => 'Approve Payment Reports',
                'description' => 'Can approve and mark payment reports as paid',
                'module' => 'payments',
            ],
            [
                'name' => 'payment-reports.export',
                'display_name' => 'Export Payment Reports',
                'description' => 'Can export payment reports to CSV/PDF',
                'module' => 'payments',
            ],
        ];

        foreach ($paymentPermissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Asignar permisos a roles
        $adminRole = Role::where('name', 'admin')->first();
        $teamLeaderRole = Role::where('name', 'team_leader')->first();
        $developerRole = Role::where('name', 'developer')->first();

        if ($adminRole) {
            // Admin tiene todos los permisos de pagos
            $adminPermissions = Permission::whereIn('name', [
                'payments.view',
                'payment-reports.view',
                'payment-reports.generate',
                'payment-reports.approve',
                'payment-reports.export',
            ])->get();
            
            $adminRole->permissions()->sync($adminPermissions->pluck('id'));
        }

        if ($teamLeaderRole) {
            // Team Leader puede ver reportes y exportar
            $teamLeaderPermissions = Permission::whereIn('name', [
                'payments.view',
                'payment-reports.view',
                'payment-reports.export',
            ])->get();
            
            $teamLeaderRole->permissions()->sync($teamLeaderPermissions->pluck('id'));
        }

        if ($developerRole) {
            // Developer solo puede ver su propio dashboard de pagos
            $developerPermissions = Permission::whereIn('name', [
                'payments.view',
            ])->get();
            
            $developerRole->permissions()->sync($developerPermissions->pluck('id'));
        }

        $this->command->info('✅ Payment permissions seeded successfully!');
    }
}
