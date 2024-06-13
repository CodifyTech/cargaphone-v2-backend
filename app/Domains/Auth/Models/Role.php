<?php

namespace Domains\Auth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as OriginalRole;

class Role extends OriginalRole
{
    use HasFactory;
    use HasUuids;

    protected $primaryKey = 'id';
    public $guard_name = 'web';
    public $incrementing = false;
    public $hidden = ['pivot'];

    protected $fillable = [
        'title',
        'name',
        'guard_name',
        'updated_at',
        'created_at',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            try {
                $model->id = (string) Str::uuid();
            } catch (\Exception $e) {
                abort(500, $e->getMessage());
            }
        });
    }
}
