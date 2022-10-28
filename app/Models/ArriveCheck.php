<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArriveCheck extends Model
{
  public $timestamps = false;
  protected $fillable = [
    'arrive_date',
    'pi_num',
    'sku_num',
    'serial_num1',
    'serial_num2',
    'if_sold',
    'memo',
  ];  

  protected $dates = ['arrive_dtae'
  ];
  protected $casts = [
	'if_sold'=>'boolean',
	'arrive_date'=>'datetime:Y-m-d',
  ];
}
