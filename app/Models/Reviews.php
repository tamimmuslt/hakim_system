<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reviews extends Model

{
    protected $primaryKey = 'review_id';

    protected $fillable = 
    [
        'user_id', 
        'reviewable_type',
         'reviewable_id', 
         'rating', 
         'comment'
        ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewable()
    {
        return $this->morphTo();
    }
}
