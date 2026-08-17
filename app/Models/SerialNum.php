<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;


class SerialNum extends Model
{
    use SoftDeletes;    

    protected $fillable = ['productSku_id','order_id','order_item_id','serial_num','ship_num','cost','created_at','deleted_at','updated_at'];

    public $timestamps = true;

    public function productSku()
    {
	return $this->belongsTo(ProductSku::class,'productSku_id');
    }

    public function order()
    {
	return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
	return $this->belongsTo(OrderItem::class);
    }

}
