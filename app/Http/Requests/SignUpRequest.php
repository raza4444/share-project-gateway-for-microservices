<?php

namespace App\Http\Requests;

/**
 * Class SignUpRequest
 * @package App\Http\Requests
 */
class SignUpRequest extends ApiRequest
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
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'unique:users',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
            'additional' => 'sometimes'
        ];
    }
}
