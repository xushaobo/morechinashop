<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerialNum extends Model
{
  protected $fillable = [
    'product_id',
	  'serialNum_id',
    'orderNum_id',
    'created_at',
    'updated_at',
  ];  

}
