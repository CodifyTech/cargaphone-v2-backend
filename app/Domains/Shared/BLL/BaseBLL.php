<?php

namespace Domains\Shared\BLL;

use App\Domains\Shared\Interfaces\IBLL;
use App\Domains\Shared\Traits\Dependencies;

/**
 * Class BaseBLL
 *
 * This class implements the IBLL interface and uses the Dependencies trait.
 * It provides basic CRUD operations and a search function.
 */
class BaseBLL implements IBLL
{
    use Dependencies;

    /**
     * Retrieves a list of items.
     *
     * @param array $options An array of options for the retrieval operation.
     * @return mixed The result of the getService()->index() operation.
     */
    public function index(array $options = [])
    {
        return $this->getService()->index($options);
    }

    /**
     * Retrieves a single item by its ID.
     *
     * @param string $id The ID of the item to retrieve.
     * @return mixed The result of the getService()->show() operation.
     */
    public function show(string $id)
    {
        return $this->getService()->show($id);
    }

    /**
     * Stores a new item.
     *
     * @param mixed $data The data of the item to store.
     * @return mixed The result of the getService()->store() operation.
     */
    public function store($data)
    {
        return $this->getService()->store($data);
    }

    /**
     * Updates an existing item by its ID.
     *
     * @param mixed $data The new data for the item.
     * @param string $id The ID of the item to update.
     * @return mixed The result of the getService()->update() operation.
     */
    public function update($data, string $id)
    {
        return $this->getService()->update($data, $id);
    }

    /**
     * Deletes an item by its ID.
     *
     * @param string $id The ID of the item to delete.
     * @return bool The result of the getService()->destroy() operation.
     */
    public function destroy(string $id)
    {
        return $this->getService()->destroy($id);
    }

    /**
     * Searches for items based on a field and value.
     *
     * @param array|string $field The field(s) to search in.
     * @param mixed $value The value to search for.
     * @param string $relation An optional relation for the search.
     * @param array $options An array of options for the search operation.
     * @return array The result of the getService()->search() operation.
     */
    public function search(array|string $field, $value, $relation = '', $options = [])
    {
        return $this->getService()->search($field, $value, $relation, $options);
    }
}
