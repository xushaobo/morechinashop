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
	return "{$this->customer_name}{$this->contact_name}{$this->contact_phone}{$this->memo}";
   }

}
