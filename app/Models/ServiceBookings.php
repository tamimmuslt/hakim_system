<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBookings extends Model
{
    protected $primaryKey = 'booking_id';

    protected $fillable = 
    [
        'user_id', 
        'service_id', 
        'booking_datetime', 
        'status', 
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Services::class, 'service_id');
    }
}
