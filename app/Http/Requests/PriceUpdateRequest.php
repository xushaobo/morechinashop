<?php

namespace App\Http\Requests;


class PriceUpdateRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
           'price' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'price.required'  => "金额",
        ];
    }
}
