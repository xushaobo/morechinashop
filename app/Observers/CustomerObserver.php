<?php

namespace App\Observers;

use App\Models\Customer;

use App\Notifications\CustomerCreated;

// creating, created, updating, updated, saving
// saved, deleting, deleted, restoring, restored

class CustomerObserver
{
   public function created(Customer $customer)
   {
	// 通知客户所有者有新的待联系客户
	$customer->user->notify(new CustomerCreated($customer));
   }
}
