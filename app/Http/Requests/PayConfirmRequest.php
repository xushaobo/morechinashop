<?php

namespace App\Http\Requests;


class PayConfirmRequest extends Request
{

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
