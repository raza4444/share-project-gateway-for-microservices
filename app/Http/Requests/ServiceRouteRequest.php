<?php

namespace App\Http\Requests;

/**
 * Class ServiceRouteRequest
 * @package App\Http\Requests
 */
class ServiceRouteRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'service_id' => 'required|exists:services,id',
            'tags' => 'required|string',
            'summary' => 'required|string',
            'type' => 'required|string|in:GET,PUT,POST,PATCH,DELETE,ANY',
            'path' => 'required|string',
            'params' => 'sometimes|array',
            'security' => 'sometimes|string|in:OAuth2,OpenId,BasicAuth,Public',
            'produces' => 'sometimes|string',
            'scope' => 'sometimes|string',
        ];
    }
}
