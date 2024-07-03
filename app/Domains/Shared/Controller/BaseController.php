<?php

namespace Domains\Shared\Controller;

use App\Domains\Shared\Interfaces\IController;
use App\Domains\Shared\Traits\Dependencies;
use App\Http\Controllers\Controller;
use Domains\Shared\Exceptions\BaseControllerException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Class BaseController
 *
 * This class extends the base Controller class and implements the IController interface.
 * It uses the Dependencies trait and provides methods for handling HTTP requests.
 *
 * @package Domains\Shared\Controller
 */
class BaseController extends Controller implements IController
{
    use Dependencies;

    /**
     * BaseController constructor.
     *
     * Applies the 'auth:sanctum' middleware to all routes except 'login', 'register', 'forgotPassword', and 'resetPassword'.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['login', 'register', 'forgotPassword', 'resetPassword', 'totemsEAnuncios']]);
    }

    /**
     * Handles the incoming request and validates it using the rules defined in the FormRequest.
     *
     * @param Request $request The incoming HTTP request.
     * @throws BaseControllerException If no FormRequest is provided in the dependencies.
     * @return Request The validated request.
     */
    protected function request(Request $request)
    {
        $createRequest = $this->getRequest();

        if (empty($createRequest)) {
            throw new BaseControllerException('Você precisa fornecer um FormRequest nas dependências.', -1);
        }

        $newRequest = $createRequest['requestClass']::createFrom($request);
        $this->validate($newRequest, $newRequest->rules(), $newRequest->messages(), $newRequest->attributes());

        return $newRequest;
    }

    /**
     * Handles a GET request to list all resources.
     *
     * @param Request $request The incoming HTTP request.
     * @return JsonResponse The list of resources in a JSON response.
     */
    public function index(Request $request)
    {
        $options = $request->all();

        return response()->json($this->getBll()->index($options));
    }

    /**
     * Handles a POST request to create a new resource.
     *
     * @param Request $request The incoming HTTP request.
     * @throws BaseControllerException If the request validation fails.
     * @throws Throwable If an error occurs during the creation of the resource.
     * @return JsonResponse The created resource in a JSON response.
     */
    public function store(Request $request)
    {
        try {
            $request = $this->request($request);
            return response()->json($this->getBll()->store($request->all()));
        } catch (Throwable $th) {
            throw $th;
        }
    }

    /**
     * Handles a GET request to display a specific resource.
     *
     * @param string $id The ID of the resource to display.
     * @return JsonResponse The specified resource in a JSON response.
     */
    public function show(string $id)
    {
        return response()->json($this->getBll()->show($id));
    }

    /**
     * Handles a PUT or PATCH request to update a specific resource.
     *
     * @param Request $request The incoming HTTP request.
     * @param string $id The ID of the resource to update.
     * @throws BaseControllerException If the request validation fails.
     * @throws Throwable If an error occurs during the update of the resource.
     * @return JsonResponse The updated resource in a JSON response.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request = $this->request($request);
            return response()->json($this->getBll()->update($request->all(), $id));
        } catch (Throwable $th) {
            throw $th;
        }
    }

    /**
     * Handles a DELETE request to remove a specific resource.
     *
     * @param string $id The ID of the resource to remove.
     * @return bool The status of the operation in a JSON response.
     */
    public function destroy(string $id)
    {
        return $this->getBll()->destroy($id);
    }

    /**
     * Handles a GET request to search for a specific resource based on the provided field and value.
     * It can also handle searching within a related resource if the relation is provided.
     *
     * @param Request $request The incoming HTTP request. The request data is used as options for the search.
     * @param string $field The field name to search by.
     * @param mixed $value The value to search for in the specified field.
     * @param string $relation (optional) The related resource to search within. Default is an empty string, which means no relation is used.
     * @return JsonResponse Returns a JSON response containing the search results.
     */
    public function search(Request $request, string $field, mixed $value, string $relation = '')
    {
        $options = $request->all();

        return response()->json($this->getBll()->search($field, $value, $relation, $options));
    }
}
