<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateClientUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-client 
                            {name : The name of the client}
                            {email : The email of the client}
                            {--password= : The password for the client (optional, will generate if not provided)}
                            {--company= : The company name of the client (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new client user with appropriate permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->option('password');
        $company = $this->option('company');

        // Validar datos de entrada
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        // Generar contraseña si no se proporciona
        if (!$password) {
            $password = $this->generatePassword();
            $this->info("Generated password: {$password}");
        }

        // Verificar que el rol Client existe
        $clientRole = Role::where('name', 'client')->first();
        if (!$clientRole) {
            $this->error('Client role not found. Please run the ClientRoleSeeder first.');
            return 1;
        }

        try {
            // Crear el usuario
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'company' => $company,
                'email_verified_at' => now(), // Verificar email automáticamente
            ]);

            // Asignar el rol Client
            $user->roles()->attach($clientRole->id);

            $this->info("Client user created successfully!");
            $this->info("Name: {$user->name}");
            $this->info("Email: {$user->email}");
            $this->info("Role: {$clientRole->display_name}");
            if ($company) {
                $this->info("Company: {$company}");
            }
            $this->info("User ID: {$user->id}");

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to create client user: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate a secure random password
     */
    private function generatePassword(): string
    {
        $length = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
}
