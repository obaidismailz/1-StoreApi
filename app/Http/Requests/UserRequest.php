<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator as IlluminateValidator;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Change this if you have specific authorization logic
    }

    public function rules()
    {
        switch ($this->getMethod()) {
            case 'POST':
                $validator = Validator::make($this->all(), [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:1',
                    //'phone' => 'nullable|string|max:20',
                    'phone' => [
                        'nullable',
                        'string',
                        'regex:/^\+\d{2}\s\d{10}$/'
                    ],
                    //'status' => 'required|integer|between:0,1',
                ]);

                if ($validator->fails()) {
                    $this->failedValidation($validator);
                }

                return [];
            default:
                return [];
        }
    }

    // Override the failedValidation method to throw an exception with validation errors
    protected function failedValidation(IlluminateValidator $validator)
    {
        $response = [
            'message' => 'Validation Error',
            'status' => 400,
            'errors' => $validator->errors()
        ];

        throw new ValidationException(
            $validator,
            response()->json(['response' => $response])
        );
    }
}
