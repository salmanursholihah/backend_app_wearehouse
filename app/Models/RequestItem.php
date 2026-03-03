<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    protected $guarded = [];

  

     public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}


