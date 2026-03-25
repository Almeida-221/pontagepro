<?php

namespace App\Models;

use App\Models\SecAffectation;
use App\Models\SecPlanning;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'gender',
        'photo',
        'email',
        'phone',
        'address',
        'password',
        'pin_code',
        'company_id',
        'zone_id',
        'role',
        'is_active',
        'is_employed',
        'salary',
        'contract_type',
        'contract_start',
        'contract_end',
        'balance',
        'profession_id',
        'category_id',
        'id_photo_front',
        'id_photo_back',
        'taux_journalier',
    ];

    protected $hidden = [
        'password',
        'pin_code',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Toutes les entreprises dont cet utilisateur est propriétaire web */
    public function ownedCompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'owner_user_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(SecZone::class, 'zone_id');
    }

    public function secAffectationActive(): HasOne
    {
        return $this->hasOne(SecAffectation::class, 'agent_id')->where('is_active', true)->latest();
    }

    /** Alias court utilisé dans les requêtes de pointage. */
    public function affectation(): HasOne
    {
        return $this->hasOne(SecAffectation::class, 'agent_id')->where('is_active', true)->latest();
    }

    public function planning(): HasOne
    {
        return $this->hasOne(SecPlanning::class, 'agent_id');
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WorkerCategory::class, 'category_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === 'company_admin';
    }
}
