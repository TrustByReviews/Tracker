<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /**
     * @var string
     */
    protected $table = 'users';
    use softdeletes, HasUuid;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nickname',
        'hour_value',
        'work_time',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'nickname' => 'string',
        'hour_value' => 'integer',
        'work_time' => 'string',
        'status' => 'string',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'project_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function qaTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'qa_assigned_to');
    }

    public function qaBugs(): HasMany
    {
        return $this->hasMany(Bug::class, 'qa_assigned_to');
    }

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin(): bool
    {
        return $this->roles()->where('value', 'admin')->exists();
    }

    /**
     * Verificar si el usuario es team leader
     */
    public function isTeamLeader(): bool
    {
        return $this->roles()->where('value', 'team_leader')->exists();
    }

    /**
     * Verificar si el usuario es developer
     */
    public function isDeveloper(): bool
    {
        return $this->roles()->where('value', 'developer')->exists();
    }

    /**
     * Verificar si el usuario es QA
     */
    public function isQa(): bool
    {
        return $this->roles()->where('value', 'qa')->exists();
    }

    /**
     * Obtener el rol principal del usuario
     */
    public function getMainRole(): ?string
    {
        $role = $this->roles()->first();
        return $role ? $role->value : null;
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class);
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(Suggestion::class, 'user_id');
    }

    public function paymentReports(): HasMany
    {
        return $this->hasMany(PaymentReport::class);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Get direct permissions for this user
     */
    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id')
            ->withPivot(['type', 'expires_at', 'reason', 'granted_by'])
            ->withTimestamps();
    }

    /**
     * Get all user permissions (direct + through roles)
     */
    public function getAllPermissions()
    {
        // Get permissions from roles
        $rolePermissions = $this->roles()->with('permissions')->get()
            ->flatMap(function ($role) {
                return $role->permissions;
            });

        // Get direct permissions
        $directPermissions = $this->directPermissions()->active()->get();

        // Merge and remove duplicates
        return $rolePermissions->merge($directPermissions)->unique('id');
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        // Check direct permissions first
        $directPermission = $this->directPermissions()
            ->where('name', $permissionName)
            ->active()
            ->exists();

        if ($directPermission) {
            return true;
        }

        // Check permissions through roles
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists();
    }

    /**
     * Check if user has any of the specified permissions
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        // Check direct permissions first
        $directPermission = $this->directPermissions()
            ->whereIn('name', $permissionNames)
            ->active()
            ->exists();

        if ($directPermission) {
            return true;
        }

        // Check permissions through roles
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionNames) {
            $query->whereIn('name', $permissionNames);
        })->exists();
    }

    /**
     * Grant a permission to user
     */
    public function grantPermission(string $permissionName, string $type = 'temporary', ?string $reason = null, ?string $expiresAt = null): bool
    {
        $permission = Permission::where('name', $permissionName)->first();
        
        if (!$permission) {
            return false;
        }

        // Check if user already has this permission
        $existingPermission = $this->directPermissions()
            ->where('permission_id', $permission->id)
            ->first();

        if ($existingPermission) {
            // Update existing permission
            $this->directPermissions()->updateExistingPivot($permission->id, [
                'type' => $type,
                'reason' => $reason,
                'expires_at' => $expiresAt,
                'granted_by' => auth()->id(),
            ]);
        } else {
            // Create new permission using UserPermission model
            \App\Models\UserPermission::create([
                'user_id' => $this->id,
                'permission_id' => $permission->id,
                'type' => $type,
                'reason' => $reason,
                'expires_at' => $expiresAt,
                'granted_by' => auth()->id(),
            ]);
        }

        return true;
    }

    /**
     * Revoke a permission from user
     */
    public function revokePermission(string $permissionName): bool
    {
        $permission = Permission::where('name', $permissionName)->first();
        
        if (!$permission) {
            return false;
        }

        return $this->directPermissions()->detach($permission->id) > 0;
    }
}
