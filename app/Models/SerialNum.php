<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SerialNum extends Model
{
    protected $fillable = ['productSku_id','serial_num'];

    public $timestamps = false;

    public function productSku()
    {
	return $this->belongsTo(ProductSku::class,'productSku_id');
    }
}
