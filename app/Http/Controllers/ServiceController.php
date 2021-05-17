<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Http\Requests\ServiceRouteRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceRouteResource;
use App\Models\ServiceRoutes;
use App\Models\Services;

/**
 * Class ServiceController
 * @package App\Http\Controllers
 */
class ServiceController extends ApiController
{
    /**
     * @param ServiceRequest $request
     * @return ServiceResource
     */
    public function store(ServiceRequest $request) {
        $input = $request->all();
        $service = Services::create($input);
        return ServiceResource::make($service);
    }


    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index() {
        $service = Services::paginate(env('PAGE_SIZE', 20));
        return ServiceResource::collection($service);
    }

    /**
     * @param ServiceRouteRequest $request
     * @return ServiceRouteResource
     */
    public function storeRoutes(ServiceRouteRequest $request, $service_id) {
        $input = $request->all();
        $input['service_id'] = $service_id;
        $service = ServiceRoutes::create($input);
        return ServiceRouteResource::make($service);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getRoutes($id) {
        $routes = ServiceRoutes::where('service_id', $id)->paginate(env('PAGE_SIZE', 20));
        return ServiceRouteResource::collection($routes);
    }
}
