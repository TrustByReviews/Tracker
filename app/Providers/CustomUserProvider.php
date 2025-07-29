<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class CustomUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        // Si el identificador es numérico (legacy), intentar encontrar un usuario válido
        if (is_numeric($identifier)) {
            // Buscar el primer usuario disponible
            $user = $this->newModelQuery()->first();
            
            if ($user) {
                // Log para debugging
                \Log::warning("Legacy user ID detected: {$identifier}, using user: {$user->id}");
                return $user;
            }
            
            return null;
        }
        
        // Para UUIDs válidos, usar el comportamiento normal
        return parent::retrieveById($identifier);
    }
    
    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        // Si el identificador es numérico (legacy), intentar encontrar un usuario válido
        if (is_numeric($identifier)) {
            // Buscar un usuario con el token remember
            $user = $this->newModelQuery()
                ->where('remember_token', $token)
                ->first();
            
            if ($user) {
                // Log para debugging
                \Log::warning("Legacy user ID with remember token detected: {$identifier}, using user: {$user->id}");
                return $user;
            }
            
            return null;
        }
        
        // Para UUIDs válidos, usar el comportamiento normal
        return parent::retrieveByToken($identifier, $token);
    }
} 