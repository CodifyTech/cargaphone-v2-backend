<?php

namespace Domains\Shared\Services;

use App\Domains\Shared\Utils\IntHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Domains\Shared\Interfaces\IService;
use App\Domains\Shared\Traits\Dependencies;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class BaseService
 *
 * This class implements the IService interface and uses the Dependencies trait.
 * It provides basic CRUD operations and a search function.
 */
class BaseService implements IService
{
    use Dependencies;

    /**
     * Retrieves a list of items.
     *
     * @param array $options An array of options for the retrieval operation.
     * @return array The result of the index operation.
     */
    public function index(array $options = [])
    {
        $data = $this->model
        ->when($options['sort_by'], function (Builder $query) use ($options) {
            $query->orderBy($options['sort_by'], $options['sort_order'] ?? 'asc');
        })
        ->paginate(IntHelper::tryParser($options['per_page'] ?? 15) ?? 15);

        return [
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
        ];
    }

    /**
     * Retrieves a single item by its ID.
     *
     * @param string $id The ID of the item to retrieve.
     * @return Model The result of the show operation.
     */
    public function show(string $id)
    {
        return $this->findById($id);
    }

    /**
     * Stores a new item.
     *
     * @param array $data The data of the item to store.
     * @return Model The result of the store operation.
     */
    public function store(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Updates an existing item by its ID.
     *
     * @param array $data The new data for the item.
     * @param string $id The ID of the item to update.
     * @return Model The result of the update operation.
     */
    public function update(array $data, string $id)
    {
        $record = $this->findById($id);
        $record->update($data);

        return $record->refresh();
    }

    /**
     * Retrieves an item by its ID.
     *
     * @param string $id The ID of the item to retrieve.
     * @return Model The result of the findById operation.
     */
    public function findById(string $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Retrieves an item by a specific column value.
     *
     * @param string $column The column to search in.
     * @param mixed $value The value to search for.
     * @param string $operator The operator to use for the search.
     * @return Model The result of the findBy operation.
     */
    public function findBy(string $column, $value, string $operator = '=')
    {
        return $this->model->where($column, $operator, $value)->firstOrFail();
    }

    /**
     * Deletes an item by its ID.
     *
     * @param string $id The ID of the item to delete.
     * @return bool The result of the destroy operation.
     */
    public function destroy($id)
    {
        $record = $this->findById($id);

        return $record->delete();
    }

    /**
     * Searches for items based on a field and value.
     *
     * @param array|string $field The field(s) to search in.
     * @param mixed $value The value to search for.
     * @param string $relation An optional relation for the search.
     * @param array $options An array of options for the search operation.
     * @return array The result of the search operation.
     */
    public function search(array|string $field, $value, string $relation = '', array $options = [])
    {
        $data = $this->model
            ->when($options['sort_by'], function (Builder $query) use ($options) {
                $query->orderBy($options['sort_by'], $options['sort_order'] ?? 'asc');
            })
            ->when($relation, function (Builder $query) use ($field, $value, $relation) {
                $query->whereHas($relation, function ($query) use ($value, $field) {
                    $query->where($field, 'like', "%$value%")->orderBy($field);
                });
            })
            ->when(!$relation, function (Builder $query) use ($value, $field) {
                $query->where($field, 'like', "%$value%")->orderBy($field);
            })
            ->paginate(IntHelper::tryParser($options['per_page'] ?? 15) ?? 15);

        return [
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
        ];
    }
}
