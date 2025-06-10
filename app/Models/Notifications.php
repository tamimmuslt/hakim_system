<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $primaryKey = 'notification_id';

    protected $fillable = 
    [
        'user_id', 
        'message_text', 
        'is_read'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
