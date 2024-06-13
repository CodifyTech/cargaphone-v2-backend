<?php

namespace App\Domains\Shared\Traits;

use Domains\Shared\BLL\BaseBLL;
use Domains\Shared\Models\Crud;
use Domains\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Model;

trait Dependencies
{
    /* @var BaseBLL $bll */
    private BaseBLL $bll;

    /* @var BaseService $service */
    private BaseService $service;

    /* @var Model $model */
    private Model $model;

    /* @var array $request */
    private array $request;


    public function __construct()
    {
        $this->bll = new BaseBLL();
        $this->service = new BaseService();
        $this->request = [];
        $this->model = new Crud;
    }

    /**
     * Get Business Logic Layer
     *
     * @return BaseBLL
     */
    public function getBll(): BaseBLL
    {
        return $this->bll;
    }

    /**
     * Set Business Logic Layer
     *
     * @param BaseBLL $bll
     */
    public function setBll(BaseBLL $bll): void
    {
        $this->bll = $bll;
    }

    /**
     * Get Service Layer
     *
     * @return BaseService
     */
    public function getService(): BaseService
    {
        return $this->service;
    }

    /**
     * Set Service Layer
     *
     * @param BaseService $service
     */
    public function setService(BaseService $service): void
    {
        $this->service = $service;
    }

    /**
     * Get HTTP Requests
     *
     * @return array
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * Set Request represents an HTTP request.
     *
     * example: $this->setRequest('request', ProductRequest::class)
     *
     * @param string $requestName
     * @param string $request
     */
    public function setRequest(string $requestName, string $request): void
    {
        $this->request = [
            'requestName' => $requestName,
            'requestClass' => $request
        ];
    }

    /**
     * Get Eloquent Model
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set Eloquent Model
     *
     * @param Model $model
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * @return array
     */
    public function getResource(): array
    {
        return $this->resource;
    }

    /**
     * @param array $resource
     */
    public function setResource(array $resource): void
    {
        $this->resource = $resource;
    }
}
