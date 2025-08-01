<?php

namespace App\Console\Commands;

use App\Models\UserPermission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:cleanup {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired user permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('🔍 Searching for expired permissions...');
        
        $expiredPermissions = UserPermission::expired()->get();
        
        if ($expiredPermissions->isEmpty()) {
            $this->info('✅ No expired permissions found.');
            return 0;
        }
        
        $this->info("📊 Found {$expiredPermissions->count()} expired permissions:");
        
        $tableData = [];
        foreach ($expiredPermissions as $permission) {
            $tableData[] = [
                'ID' => $permission->id,
                'User' => $permission->user->name,
                'Permission' => $permission->permission->display_name,
                'Type' => $permission->type,
                'Expired At' => $permission->expires_at,
                'Reason' => $permission->reason ?: 'N/A',
            ];
        }
        
        $this->table(['ID', 'User', 'Permission', 'Type', 'Expired At', 'Reason'], $tableData);
        
        if ($isDryRun) {
            $this->info('🔍 Dry run mode - no permissions were deleted.');
            return 0;
        }
        
        if (!$this->confirm('Do you want to delete these expired permissions?')) {
            $this->info('❌ Operation cancelled.');
            return 0;
        }
        
        $deletedCount = 0;
        foreach ($expiredPermissions as $permission) {
            try {
                $permission->delete();
                $deletedCount++;
                
                Log::info('Expired permission deleted', [
                    'permission_id' => $permission->id,
                    'user_id' => $permission->user_id,
                    'permission_name' => $permission->permission->name,
                    'expired_at' => $permission->expires_at,
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to delete permission {$permission->id}: {$e->getMessage()}");
                Log::error('Failed to delete expired permission', [
                    'permission_id' => $permission->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->info("✅ Successfully deleted {$deletedCount} expired permissions.");
        
        return 0;
    }
} 