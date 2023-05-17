<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;

use App\Http\Requests\CustomerRequest;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
	return view('customers.index',[
		'customers' => $request->user()->customers,	   
	]);
    }
    
    public function create()
    {
	return view('customers.create_and_edit',['customer' => new Customer()]);	
    }

    public function store(CustomerRequest $request)
    {
	$request->user()->customers()->create($request->only([
	    'customer_name',
	    'contact_name',
	    'contact_phone',
	    'memo',
	]));	
	
	return redirect()->route('customers.index');
    }
    public function edit(Customer $customer)
    {
	return view('customers.create_and_edit', ['customer'=> $customer]);
    }
    public function update(Customer $customer, CustomerRequest $request)
    {
	$customer->update($request->only([
		'customer_name',	
		'contact_name',	
		'contact_phone',	
	]));
	
	return redirect()->route('customers.index');
    }
}
