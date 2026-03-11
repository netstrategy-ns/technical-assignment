<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\Tables\UserTableColumns;
use App\Models\Concerns\HasAdminTable;
use App\Models\Scopes\User\FilterScopes;
use App\Models\Scopes\User\SortScopes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;
    use HasAdminTable;
    use FilterScopes;
    use SortScopes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'public_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * The attributes that should be hidden for serialization.
     * is_admin viene reso visibile solo nelle liste amministrative quando necessario.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        'is_admin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_confirmed_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    // Gestione colonne tabella dashboard admin
    public static function tableColumns(): array
    {
        return UserTableColumns::columns();
    }


    /**
     * ------------------------------------------------------------
     * RELATIONS
     * ------------------------------------------------------------
     * 
     */

     public function holds(): HasMany
     {
         return $this->hasMany(Hold::class);
     }
 
     public function orders(): HasMany
     {
         return $this->hasMany(Order::class);
     }

    /**
     * ------------------------------------------------------------
     * HELPERS
     * ------------------------------------------------------------
     */

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

}
