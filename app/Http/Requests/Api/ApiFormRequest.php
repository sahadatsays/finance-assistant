<?php

namespace App\Http\Requests\Api;

use App\Support\Api\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError(
                'The given data was invalid.',
                $validator->errors()->toArray(),
            ),
        );
    }
}
