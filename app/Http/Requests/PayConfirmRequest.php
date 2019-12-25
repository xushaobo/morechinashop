<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayConfirmRequest extends FormRequest
{

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
           'data' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'data.required'  => '原因',
        ];
    }
}
