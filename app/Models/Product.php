<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'title', 'description', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price',
	'type',
    ];
    protected $casts = [
        'on_sale' => 'bollean', // on_sale是一个布尔类型的字段
    ];

    const TYPE_NORMAL = 'normal';
    const TYPE_SERIALNUM = 'serialnum';
    public static $typeMap = [
	    self::TYPE_NORMAL => '普通商品',
	    self::TYPE_SERIALNUM => '产品序列号',
	];

    //与商品SKU关联
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageUrlAttribute()
    {
        //如果image字段本身就已经是完整的url就直接返回
        if(Str::startsWith($this->attributes['image'],['http://','https://'])) {
            return $this->attributes['image'];
        }

        return \Storage::disk('public')->url($this->attributes['image']);
    }
    public function properties()
    {
        return $this->hasMany(ProductProperty::class);
    }

    public function serialnum()
    {
	return $this->hasOne(SerialNum::class);
    }
}
