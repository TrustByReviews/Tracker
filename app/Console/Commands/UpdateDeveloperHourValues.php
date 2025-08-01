<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateDeveloperHourValues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'developers:update-hour-values {--value=25 : Hour value to set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update hour values for all developers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hourValue = $this->option('value');

        $developers = User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })->get();

        $this->info("Updating hour values for {$developers->count()} developers to \${$hourValue}/hr...");

        $updated = 0;
        foreach ($developers as $developer) {
            $developer->update(['hour_value' => $hourValue]);
            $this->line("   - {$developer->name}: \${$hourValue}/hr");
            $updated++;
        }

        $this->info("✅ Updated {$updated} developers successfully!");
    }
}
