<?php

namespace App\Http\Requests;


class CustomerRequest extends Request
{

    public function rules()
    {
        return [
	    'customer_name' => 'required',
	    'contact_name' => 'required',
	    'contact_phone' => 'required',
	    'memo' => 'required',
        ];
    }
}
