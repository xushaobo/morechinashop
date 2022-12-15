<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;


class SerialNum extends Model
{
    use SoftDeletes;    

    protected $fillable = ['productSku_id','serial_num','ship_num','created_at'];

    public $timestamps = true;

    public function productSku()
    {
	return $this->belongsTo(ProductSku::class,'productSku_id');
    }
}
