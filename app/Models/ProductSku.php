<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductSku extends Model
{
    protected $fillable = ['title', 'description','image','price','stock_price','stock'];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function decreaseStock($amount)
    {
        if ($amount < 0){
            throw new InternalException('减库存不可小于0');
        }

        return $this->newQuery()->where('id',$this->id)->where('stock','>=',$amount)->decrement('stock',$amount);
    }

    public function addStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('加库存不可小于0');
        }
        $this->increment('stock',$amount);
    }
    public function serialNum()
    {
	return $this->hasMany(SerialNum::class,'productSku_id');
    }
    public function getImageUrlAttribute()
    {
	// 如果 image 字段本身就已经是完整的url就直接返回
	if (Str::startsWith($this->attributes['img'],['http://','https://'])) {
	   return $this->attributes['img'];
	}
	return \Storage::disk('public')->url($this->attributes['img']);
    }
}
