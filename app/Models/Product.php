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
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function productrequests()
    {
        return $this->hasMany(ProductRequest::class);
    }
}
