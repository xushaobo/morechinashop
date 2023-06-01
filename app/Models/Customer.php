<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
   protected $fillable = [
	'customer_name',
	'contact_name',
	'contact_phone',
	'memo',
	'last_used_at',
   ];
   
   protected $dates = ['last_used_at'];
	
   public function user()
   {
	return $this->belongsTo(User::class);
   }
   
   public function getFullCustomerAttribute()
   {
	return "客户名称：{$this->customer_name}，
	        联系人：{$this->contact_name} 
		        {$this->contact_phone}
                ，备注：{$this->memo}";
   }

}
