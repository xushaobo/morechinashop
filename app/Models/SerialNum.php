<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerialNum extends Model
{
  protected $fillable = [
	  'serialNum_id',
	  'orderNum_id'
  ];  

  public function serialnum()
  {
	  return $this->belongsTo(ProductSku::class);
  }
}
