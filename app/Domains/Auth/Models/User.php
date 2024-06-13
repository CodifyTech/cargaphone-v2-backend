<?php

namespace Domains\Auth\Models;

use Domains\Shared\Traits\Uuid;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasUuids, Uuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'foto',
        'ativo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'roles'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = [
        'role',
    ];

    protected $primaryKey = 'id';
    protected $table = 'users';

    public function role(): Attribute
    {
        return Attribute::make(
            get: function () {
                return  [
                    'title' => $this->roles[0]->title,
                    'value' => $this->roles[0]->name,
                ] ?? null;
            },
        );
    }

    protected function foto(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!empty($value)) {
                    return Storage::disk('s3')->url("fotos_usuario/$value");
                }

                return null;
            },
        );
    }
}
