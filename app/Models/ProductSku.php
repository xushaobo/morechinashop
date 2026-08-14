<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductSku extends Model
{
    protected $fillable = ['title', 'description','price','stock_price','stock', 'master_sku_id','root_sku_id','total_stock'];

    protected static function boot()
    {
        parent::boot();

        static::saved(function (ProductSku $sku) {
            $rootSkuId = $sku->resolveRootSkuId();

            if (!$rootSkuId) {
                return;
            }

            if ((int) $sku->root_sku_id !== (int) $rootSkuId) {
                static::where('id', $sku->id)->update(['root_sku_id' => $rootSkuId]);
                $sku->root_sku_id = $rootSkuId;
            }

            static::where('master_sku_id', $sku->id)->update(['root_sku_id' => $rootSkuId]);
        });
    }

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
	return $this->belongsTo(ProductSku::class, 'master_sku_id');
    }
    // 主 SKU 拥有的所有子 SKU
    public function childSkus()
    {
	return $this->hasMany(ProductSku::class, 'master_sku_id');
    }
    // 获取总库存 (主 SKU = 自身库存 + 所有子SKU库存; 子 SKU = 自身库存)
    public function  getTotalStockAttribute()
    {
        $rootSkuId = $this->root_sku_id ?: $this->resolveRootSkuId();

        if (!$rootSkuId) {
            return $this->stock ?: 0;
        }

        return self::where(function ($query) use ($rootSkuId) {
            $query->where('root_sku_id', $rootSkuId)
                ->orWhere('id', $rootSkuId)
                ->orWhere('master_sku_id', $rootSkuId);
        })->sum('stock');
    }

    protected function resolveRootSkuId()
    {
        if ($this->master_sku_id) {
            $masterSku = static::find($this->master_sku_id);

            if ($masterSku) {
                return $masterSku->root_sku_id ?: $masterSku->id;
            }
        }

        return $this->id;
    }
    
}
