<?php

namespace App\Http\Requests;

/**
 * Class ServiceRequest
 * @package App\Http\Requests
 */
class ServiceRequest extends ApiRequest
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
            'hostname' => 'required|string|unique:services,hostname',
            'url' => 'required|string|unique:services,url',
            'throttling' => 'sometimes|integer|min:10|max:999',
            'secure' => 'required|in:0,1',
        ];
    }
}
