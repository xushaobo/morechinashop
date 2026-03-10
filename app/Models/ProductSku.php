<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductSku extends Model
{
    protected $fillable = ['title', 'description','price','stock_price','stock', 'master_sku_id'];

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
    public function productSkuImage()
    {
	return $this->hasMany(ProductSkuImage::class);
    }
    public function show($id)
    {
        $productSku = ProductSku::with('productSkuImage')->find($id);
    }
    // 子SKU所属的主SKU
    public function masterSku()
    {
	return $this->belogsTo(ProductSku::class, 'master_sku_id');
    }
    // 主 SKU 拥有的所有子 SKU
    public function childSkus()
    {
	return $this->hasMany(ProductSku::class, 'master_sku_id');
    }
    // 获取总库存 (主 SKU = 自身库存 + 所有子SKU库存; 子 SKU = 自身库存)
    public function  getTotalStockAttribute()
    {
	if ($this->master_sku_id)
	{
	  //子SKU,总库存就是自身库存
	  return $this->stock;
	}
	// 主SKU,自身库存 + 子SKU库存
        return $this->stock + $this->childSkus()->sum('stock');
    }
    
}
