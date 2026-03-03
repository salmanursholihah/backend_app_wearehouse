<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reviewed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
