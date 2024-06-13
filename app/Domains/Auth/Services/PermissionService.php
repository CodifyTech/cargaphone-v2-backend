<?php

namespace Domains\Auth\Services;

use Domains\Auth\Models\User;
use Illuminate\Support\Carbon;
use Domains\Auth\Models\Permission;
use App\Domains\Shared\Utils\IntHelper;
use Domains\Shared\Services\BaseService;
use App\Domains\Auth\Enums\PermissionActionsEnum;

class PermissionService extends BaseService
{
    public function __construct(private readonly Permission $permission)
    {
        $this->setModel($this->permission);
    }

    public function index(array $options = [])
    {
        $data = $this->permission->permissionsFormat($this->permission)
                ->when(!auth()->user()->hasRole('admin'), function ($query) {
                    return $query->where('subject', '=', 'all');
                })
                ->where('subject', '!=', 'Auth');

        // Apply search filter if search option is provided
        if (isset($options['search'])) {
            $searchTerm = strtolower($options['search']);

            $data = $data->filter(function ($item) use ($searchTerm) {
                return strpos(strtolower($item['title']), $searchTerm) !== false;
            });
        }

        // Sort data if sort_by and sort_direction are provided
        if (isset($options['sortBy'])) {
            $sortBy = $options['orderBy'];
            $sortDirection = $options['sortBy'] ?? 'asc';

            $data = $data->sortBy($sortBy, SORT_REGULAR, $sortDirection === 'desc');
        }

        // Pagination
        $perPage = IntHelper::tryParser($options['per_page'] ?? 15) ?? 15;
        $currentPage = $options['page'] ?? 1;
        $startIndex = ($currentPage - 1) * $perPage;
        $pagedData = $data->slice($startIndex, $perPage);

        return [
            'data' => $pagedData->values(), // Reset keys after slicing
            'page' => (int) $currentPage,
            'total' => $data->count(),
        ];
    }

    public function listAll(){
        return $this->permission->permissionsFormat($this->permission)
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('subject', '=', 'all');
            })
            ->where('subject', '!=', 'Auth')
            ->values();
    }

    private function createPermissions(array $data)
    {
        $actions = [];

        if ($data['actions']) {
            foreach ($data['actions'] as $action) {
                if (isset($data['title']) && isset($data['name']) && isset($action)) {
                    $actions[] = Permission::create([
                        'title' => $data['title'],
                        'name' => "{$data['name']} {$action}",
                    ]);
                }
            }
        }

        return $actions;
    }

    private function getPermissionActions($data)
    {
        return $this->permission->where('name', 'LIKE', '%'.$data['name'].'%')->get()->reduce(function ($acc = [], $item) {
            $permissionAction = explode(' ', $item->name);

            $acc[] = [
                'id' => $item->id,
                'title' => PermissionActionsEnum::from($permissionAction[1])->label(),
                'action' => $permissionAction[1] ?? '', // Assuming the action is always the second word
            ];

            return $acc;
        }, []);
    }

    public function store(array $data)
    {
        try {
            $data['name'] = \Str::camel($data['name']);
            $this->createPermissions($data);

            return [
                'title' => $data['title'],
                'subject' => $data['name'],
                'created_at' => Carbon::now()->format('d/m/Y H:i:s'),
                'actions' => $this->getPermissionActions($data),
            ];
        } catch (\Exception $exception) {
            return new \Exception('Ocorreu um erro não criar permissão.');
        }
    }

    public function update(array $data, string $id)
    {
        if ($id === 'updateAll') {
            if (isset($data['actionsDelete'])) {
                $actionsAddedIds = array_column($data['actionsAdded'] ?? [], 'id');
                $data['actionsAdded'] = array_filter($data['actionsDelete'] ?? [], function ($action) use ($actionsAddedIds) {
                    return in_array($action, $actionsAddedIds);
                });
            }

            foreach ($data['actionsAdded'] as $action) {
                $permission = $this->permission->find($action['id']);

                if (isset($data['name']) && isset($data['title']) && isset($action['action'])) {
                    $name = \Str::camel($data['name']);

                    $permission->update([
                        'title' => $data['title'],
                        'name' => "{$name} {$action['action']}",
                    ]);
                }
            }

            if (isset($data['actionsAdding'])) {
                foreach ($data['actionsAdding'] as $action) {
                    if (isset($data['name']) && isset($data['title']) && isset($action)) {
                        Permission::create([
                            'title' => $data['title'],
                            'name' => "{$data['name']} {$action}",
                        ]);
                    }
                }
            }

            if (isset($data['actionsDelete'])) {
                foreach ($data['actionsDelete'] as $action) {
                    $this->permission->find($action)->delete();
                }
            }

            return [
                'title' => $data['title'],
                'subject' => $data['name'],
                'created_at' => Carbon::now()->format('d/m/Y H:i:s'),
                'actions' => $this->getPermissionActions($data),
            ];
        } else {
            $permission = $this->permission->find($id);
            $permission->update($data);

            return [
                'title' => $data['title'],
                'subject' => $data['name'],
                'created_at' => Carbon::now()->format('d/m/Y H:i:s'),
                'actions' => $this->getPermissionActions($data),
            ];
        }
    }

    public function destroyAll($data)
    {
        try {
            \DB::beginTransaction();
            if (isset($data['dados'])) {
                foreach ($data['dados'] as $id) {
                    $permission = $this->permission->find($id);
                    $permission->delete();
                }
            }
            \DB::commit();

            return request()->json([
                'message' => 'Todas as permissões foram excluídas com sucesso',
            ]);
        } catch (\Exception $exception) {
            \DB::rollBack();
            throw new \Exception('Ocorreu um erro ao excluir as permissões.');
        }
    }

    public function listarAcoes()
    {
        return collect(PermissionActionsEnum::cases())->reduce(function ($acc = [], $item) {
            $acc[] = [
                'id' => $item->value,
                'title' => $item->label(),
            ];

            return $acc;
        });
    }

    public function atribuirUserPermission(User $user, Permission $permission)
    {
        return $user->givePermissionTo($permission->name);
    }

    public function removerUserPermission($user, $permission)
    {
        return $user->revokePermissionTo($permission->name);
    }
}
