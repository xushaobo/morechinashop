<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;

use App\Http\Requests\CustomerRequest;

use App\Notifications\CustomerCreated;

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
	$customer->user->notify(new CustomerCreated($customer));
    }

    public function store(CustomerRequest $request)
    {
	$request->user()->customers()->create($request->only([
	    'customer_name',
	    'contact_name',
	    'contact_phone',
	    'memo',
	    'last_used_at',
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
		'memo',
		'last_used_at',
	]));
	
	return redirect()->route('customers.index');
    }
    public function destory(Customer $customer)
    {
	$customer->delete();	
	
	return [];
    }
}
