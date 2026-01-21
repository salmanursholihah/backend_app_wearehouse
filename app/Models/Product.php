<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

protected $guarded = [];


public function requests()
    {
        return $this->belongsToMany(Request::class, 'request_items', 'product_id', 'request_id')
                    ->withPivot('quantity', 'status')
                    ->withTimestamps();
    }
}




