<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Admin permissions
            [
                'name' => 'admin.dashboard',
                'display_name' => 'Access Admin Dashboard',
                'description' => 'Can access the admin dashboard',
                'module' => 'admin',
            ],
            [
                'name' => 'admin.users',
                'display_name' => 'Manage Users',
                'description' => 'Can view, create, edit and delete users',
                'module' => 'admin',
            ],
            [
                'name' => 'admin.roles',
                'display_name' => 'Manage Roles',
                'description' => 'Can view, create, edit and delete roles',
                'module' => 'admin',
            ],
            [
                'name' => 'admin.permissions',
                'display_name' => 'Manage Permissions',
                'description' => 'Can view, create, edit and delete permissions',
                'module' => 'admin',
            ],
            [
                'name' => 'admin.system',
                'display_name' => 'System Administration',
                'description' => 'Can access system settings and configurations',
                'module' => 'admin',
            ],

            // Project permissions
            [
                'name' => 'projects.view',
                'display_name' => 'View Projects',
                'description' => 'Can view projects',
                'module' => 'projects',
            ],
            [
                'name' => 'projects.create',
                'display_name' => 'Create Projects',
                'description' => 'Can create new projects',
                'module' => 'projects',
            ],
            [
                'name' => 'projects.edit',
                'display_name' => 'Edit Projects',
                'description' => 'Can edit existing projects',
                'module' => 'projects',
            ],
            [
                'name' => 'projects.delete',
                'display_name' => 'Delete Projects',
                'description' => 'Can delete projects',
                'module' => 'projects',
            ],
            [
                'name' => 'projects.manage',
                'display_name' => 'Manage Projects',
                'description' => 'Full project management capabilities',
                'module' => 'projects',
            ],

            // Task permissions
            [
                'name' => 'tasks.view',
                'display_name' => 'View Tasks',
                'description' => 'Can view tasks',
                'module' => 'tasks',
            ],
            [
                'name' => 'tasks.create',
                'display_name' => 'Create Tasks',
                'description' => 'Can create new tasks',
                'module' => 'tasks',
            ],
            [
                'name' => 'tasks.edit',
                'display_name' => 'Edit Tasks',
                'description' => 'Can edit existing tasks',
                'module' => 'tasks',
            ],
            [
                'name' => 'tasks.delete',
                'display_name' => 'Delete Tasks',
                'description' => 'Can delete tasks',
                'module' => 'tasks',
            ],
            [
                'name' => 'tasks.assign',
                'display_name' => 'Assign Tasks',
                'description' => 'Can assign tasks to users',
                'module' => 'tasks',
            ],
            [
                'name' => 'tasks.approve',
                'display_name' => 'Approve Tasks',
                'description' => 'Can approve completed tasks',
                'module' => 'tasks',
            ],
            [
                'name' => 'tasks.reject',
                'display_name' => 'Reject Tasks',
                'description' => 'Can reject completed tasks',
                'module' => 'tasks',
            ],
            [
                'name' => 'tasks.manage',
                'display_name' => 'Manage Tasks',
                'description' => 'Full task management capabilities',
                'module' => 'tasks',
            ],

            // Sprint permissions
            [
                'name' => 'sprints.view',
                'display_name' => 'View Sprints',
                'description' => 'Can view sprints',
                'module' => 'sprints',
            ],
            [
                'name' => 'sprints.create',
                'display_name' => 'Create Sprints',
                'description' => 'Can create new sprints',
                'module' => 'sprints',
            ],
            [
                'name' => 'sprints.edit',
                'display_name' => 'Edit Sprints',
                'description' => 'Can edit existing sprints',
                'module' => 'sprints',
            ],
            [
                'name' => 'sprints.delete',
                'display_name' => 'Delete Sprints',
                'description' => 'Can delete sprints',
                'module' => 'sprints',
            ],
            [
                'name' => 'sprints.manage',
                'display_name' => 'Manage Sprints',
                'description' => 'Full sprint management capabilities',
                'module' => 'sprints',
            ],

            // Report permissions
            [
                'name' => 'reports.view',
                'display_name' => 'View Reports',
                'description' => 'Can view reports',
                'module' => 'reports',
            ],
            [
                'name' => 'reports.create',
                'display_name' => 'Create Reports',
                'description' => 'Can create new reports',
                'module' => 'reports',
            ],
            [
                'name' => 'reports.export',
                'display_name' => 'Export Reports',
                'description' => 'Can export reports to different formats',
                'module' => 'reports',
            ],
            [
                'name' => 'reports.manage',
                'display_name' => 'Manage Reports',
                'description' => 'Full report management capabilities',
                'module' => 'reports',
            ],

            // Team Leader permissions
            [
                'name' => 'team-leader.dashboard',
                'display_name' => 'Access Team Leader Dashboard',
                'description' => 'Can access the team leader dashboard',
                'module' => 'team-leader',
            ],
            [
                'name' => 'team-leader.approve',
                'display_name' => 'Approve Team Work',
                'description' => 'Can approve team member work',
                'module' => 'team-leader',
            ],
            [
                'name' => 'team-leader.manage',
                'display_name' => 'Manage Team',
                'description' => 'Full team management capabilities',
                'module' => 'team-leader',
            ],

            // User permissions
            [
                'name' => 'users.view',
                'display_name' => 'View Users',
                'description' => 'Can view user profiles',
                'module' => 'users',
            ],
            [
                'name' => 'users.edit',
                'display_name' => 'Edit Users',
                'description' => 'Can edit user profiles',
                'module' => 'users',
            ],
            [
                'name' => 'users.manage',
                'display_name' => 'Manage Users',
                'description' => 'Full user management capabilities',
                'module' => 'users',
            ],

            // Permission management
            [
                'name' => 'permissions.grant',
                'display_name' => 'Grant Permissions',
                'description' => 'Can grant permissions to users',
                'module' => 'permissions',
            ],
            [
                'name' => 'permissions.revoke',
                'display_name' => 'Revoke Permissions',
                'description' => 'Can revoke permissions from users',
                'module' => 'permissions',
            ],
            [
                'name' => 'permissions.manage',
                'display_name' => 'Manage Permissions',
                'description' => 'Full permission management capabilities',
                'module' => 'permissions',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
} 