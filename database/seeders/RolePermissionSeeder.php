<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $teamLeaderRole = Role::where('name', 'team_leader')->first();
        $developerRole = Role::where('name', 'developer')->first();

        if (!$adminRole || !$teamLeaderRole || !$developerRole) {
            $this->command->error('Roles not found. Please run RoleSeeder first.');
            return;
        }

        // Admin permissions - Full access
        $adminPermissions = Permission::all();
        $adminRole->permissions()->sync($adminPermissions->pluck('id'));

        // Team Leader permissions
        $teamLeaderPermissions = Permission::whereIn('name', [
            'team-leader.dashboard',
            'team-leader.approve',
            'team-leader.manage',
            'projects.view',
            'projects.edit',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.assign',
            'tasks.approve',
            'tasks.reject',
            'sprints.view',
            'sprints.edit',
            'reports.view',
            'reports.create',
            'reports.export',
            'users.view',
            'users.edit',
        ])->get();
        $teamLeaderRole->permissions()->sync($teamLeaderPermissions->pluck('id'));

        // Developer permissions
        $developerPermissions = Permission::whereIn('name', [
            'projects.view',
            'tasks.view',
            'tasks.edit',
            'sprints.view',
            'reports.view',
        ])->get();
        $developerRole->permissions()->sync($developerPermissions->pluck('id'));

        $this->command->info('Role permissions assigned successfully!');
        $this->command->info("Admin: {$adminPermissions->count()} permissions");
        $this->command->info("Team Leader: {$teamLeaderPermissions->count()} permissions");
        $this->command->info("Developer: {$developerPermissions->count()} permissions");
    }
} 