<?php

namespace App\Http\Requests;


class CustomerRequest extends Request
{

    public function rules()
    {
        return [
	    'customer_name' => 'required',
	    'last_used_at' => 'required',
        ];
    }
}
