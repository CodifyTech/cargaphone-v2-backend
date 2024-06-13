<?php

namespace Domains\Auth\Models;

use App\Domains\Auth\Enums\PermissionActionsEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission as OriginalPermission;

class Permission extends OriginalPermission
{
    use HasUuids;

    public $guard_name = 'web';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'name',
        'guard_name',
        'updated_at',
        'created_at',
    ];

    public const ORDER_ACTION = ['create', 'read', 'update', 'delete', 'list', 'edit', 'show', 'destroy', 'store', 'index', 'create', 'edit', 'update', 'delete', 'destroy', 'restore', 'block'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public static function permissionsFormat($permission)
    {
        $permissionsGroup = collect();
        $data = $permission;

        if (!auth()->user()->hasRole('admin')) {
            $data = $data->where('name', '=', 'all manage');
        }

        $data->get()->each(function ($item) use (&$permissionsGroup) {
            $permissionAction = explode(' ', $item->name);

            if ($permissionAction[0] !== '') {
                $existingGroup = $permissionsGroup->where('subject', $permissionAction[0])->first();

                if ($existingGroup) {
                    $permissionsGroup = $permissionsGroup->map(function ($group) use ($permissionAction, $item) {
                        if ($group['subject'] === $permissionAction[0]) {
                            $group['actions'][] = [
                                'id' => $item->id,
                                'title' => PermissionActionsEnum::from($permissionAction[1])->label(),
                                'action' => $permissionAction[1] ?? '', // Assuming the action is always the second word
                            ];
                            // Ordena as ações de acordo com $orderAction
                            usort($group['actions'], function ($a, $b) {
                                $aIndex = array_search($a['action'], self::ORDER_ACTION);
                                $bIndex = array_search($b['action'], self::ORDER_ACTION);

                                return $aIndex - $bIndex;
                            });
                        }

                        return $group;
                    });
                } else {
                    $permissionsGroup->push([
                        'title' => $item->title,
                        'subject' => $permissionAction[0],
                        // ...

                        'created_at' => Carbon::parse($item->created_at)->format('d/m/Y H:i:s'),
                        'actions' => [
                            [
                                'id' => $item->id,
                                'title' => PermissionActionsEnum::from($permissionAction[1])->label(),
                                'action' => $permissionAction[1] ?? '', // Assuming the action is always the second word
                            ],
                        ],
                    ]);
                }
            }
        });

        return $permissionsGroup->values();
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

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
