<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;



class StoreFormulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules()
    {
        return [

            'window_type_id' => ['required','exists:window_types,id'],
            'scop' => ['required','string'],
            'output_variable_id' => ['required','exists:variables,id'],
            'formula_tokens' => ['required','array','min:1'],
            'condition_tokens' => ['nullable','array','min:1'],
        ];
    }
}