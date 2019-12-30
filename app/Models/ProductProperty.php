<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductProperty extends Model
{

    protected $fillable = ['name','value'];
    public $timestamps = false;

    public function prodduct()
    {
        return $this->belongsTo(Product::class);
    }

}
