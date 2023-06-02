<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
   protected $fillable = [
	'id',
	'customer_name',
	'contact_name',
	'contact_phone',
	'memo',
	'last_used_at',
	'first_call',
	'region',
	'city',
	'customer_type',
	'contact_info',
	'contact_QQ',
	'wechat',
	'contact_email',
	'contact_address',
	'contact_require',
	'require_type',
	'require_brand',
	'follow_up_stage',
	'recent_contact',
	'next_action',
   ];
   
   protected $dates = ['last_used_at'];
   protected $first_call_date = ['first_used_at'];
	
   public function user()
   {
	return $this->belongsTo(User::class);
   }
   
   public function getFullCustomerAttribute()
   {
	return "
		ID：{$this->id}:
		客户名称：{$this->customer_name}，
	        联系人：{$this->contact_name} 
		        {$this->contact_phone}
                ，备注：{$this->memo}";
   }

}
