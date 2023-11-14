<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator as IlluminateValidator;

class PostRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Change this if you have specific authorization logic
    }

    public function rules()
    {
        switch ($this->getMethod()) {
            case 'POST':
                // Validation rules for creating a new post
                return [
                    'name' => 'required|string|max:255',
                    'description' => 'required|string',
                    'image_path' => 'required',
                    //'status' => 'required|integer|between:0,1',
                ];

            case 'PUT':
                $rules = [];

                // Check if the 'name' field is present in the request
                if ($this->has('name')) {
                    $rules['name'] = 'string|max:255';
                }

                // Check if the 'description' field is present in the request
                if ($this->has('description')) {
                    $rules['description'] = 'string';
                }

                // Check if the 'image_path' field is present in the request
                if ($this->has('image_path')) {
                    $rules['image_path'] = 'required';
                }

                // Check if the 'status' field is present in the request
                if ($this->has('status')) {
                    $rules['status'] = 'integer|between:0,1';
                }

                // If no fields are being updated, no validation rules will be applied
                return $rules;

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
