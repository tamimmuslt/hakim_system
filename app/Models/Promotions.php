<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotions extends Model
{
    protected $primaryKey = 'promotion_id';

    protected $fillable = [
        'center_id',
         'title',
          'description',
        'start_date',
         'end_date',
         'discount_percent',
          'price_after_discount',
           'is_active'
    ];

    public function center()
    {
        return $this->belongsTo(Centers::class, 'center_id');
    }
}
