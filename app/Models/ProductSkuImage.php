<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductSkuImage extends Model
{
  protected $fillable = ['productsku_id','path','is_main','order','created_at','updated_at'];
  
  protected $timestamp = true;

  public static function booted()
  {
     static::deleting(function ($productSkuImage) {
	//删除物理文件
	if (!$productSkuImage instanceof \Illuminate\Database\Eloquent\Model) {
	    throw new \Exception('Invalid model instance');
        }
	Storage::disk('public')->delete($productSkuImage->path);
     });
  }

  public function productSku()
  {
	return $this->belongsTo(ProductSku::class);
  }
}
