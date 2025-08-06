<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class GetUserIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:show-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users with their UUIDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::select('id', 'name', 'email')->get();
        
        $this->info('Usuarios disponibles:');
        $this->newLine();
        
        foreach ($users as $user) {
            $this->line("- ID: {$user->id}");
            $this->line("  Nombre: {$user->name}");
            $this->line("  Email: {$user->email}");
            $this->newLine();
        }
        
        $this->info("Total de usuarios: " . $users->count());
        
        return Command::SUCCESS;
    }
}
