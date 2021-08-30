<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
  public $timestamps = false;
  protected $fillable = [
	'repair_date','customer_name','phone','seller','brand','type','serial_num','bad_description','ifunderwarry','howtodo','des_add','finfish_date','mail_info','ifreturntoBJ','howtodo_BJ','returnBJ_date','remark'
  ];
  
  protected $dates = ['repair_dtae','finfish_date','returnBJ_date'
  ]; 

  protected $casts = [
	'ifunderwarry'=>'boolean',
	'ifreturntoBJ'=>'boolean',
	'repair_date'=>'datetime:Y-m-d',
  ];
}
