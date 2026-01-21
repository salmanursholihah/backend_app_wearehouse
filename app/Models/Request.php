<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $guarded = [];

 public function items()
    {
        return $this->hasMany(RequestItem::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'request_items', 'request_id', 'product_id')
                    ->withPivot('quantity', 'status')
                    ->withTimestamps();
    }

}



