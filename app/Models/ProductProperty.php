<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductProperty extends Model
{

    protected $fillable = ['name','value'];

    public function prodduct()
    {
        return $this->belongsTo(Product::class);
    }

}
