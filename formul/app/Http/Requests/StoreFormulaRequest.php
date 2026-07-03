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
            'name' => ['required','string'],
            'code' => ['required','string'],
            'output_variable_id' => ['required','exists:variables,id'],
            'execution_order' => ['required','integer'],
            'tokens' => ['required','array','min:1'],
        ];
    }
}