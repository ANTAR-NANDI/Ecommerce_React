<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Role;

#[Fillable(['name', 'email', 'password', 'role', 'warehouse_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
    public function permissions(): array { return $this->isSuperAdmin() ? ['*'] : (Role::where('slug', $this->role)->value('permissions') ?? []); }
    public function canAccessModule(string $module): bool { $permissions = $this->permissions(); return in_array('*', $permissions, true) || in_array($module, $permissions, true); }
}
