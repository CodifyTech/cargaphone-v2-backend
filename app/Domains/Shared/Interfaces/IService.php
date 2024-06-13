<?php

namespace App\Domains\Shared\Interfaces;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface IService{

    /**
     * @param array $options (path, query, fragment, pageName)
     */
    public function index(array $options = []);

    /**
     * Display the specified resource.
     * @param string $id
     */
    public function show(string $id);

    /**
     * Show the form for editing the specified resource.
     *
     * @param array $data
     */
    public function store(array $data);

    /**
     * Update the specified resource in storage.
     *
     * @param array $data
     * @param string $id
     */
    public function update(array $data, string $id);

    /**
     * Find
     *
     * @param string $id
     */
    public function findById(string $id);

    /**
     * Search
     *
     * @param string|array $field
     * @param $value
     */
    public function search(string|array $field, $value);

    /**
     * Remove the specified resource from storage.
     * @param $id
     */
    public function destroy($id);
}
