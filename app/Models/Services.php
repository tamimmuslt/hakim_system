<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    protected $primaryKey = 'service_id';

    protected $fillable = ['name', 'description'];

    public function centers() 
    {
        return $this->belongsToMany(Centers::class,'center_services','service_id','center_id')->withPivot('price')->withTimestamps();

    }
    public function booking()
     {
        return $this->hasMany(ServiceBookings::class,'service_id');
    }

    public function reviews() 
    {
        return $this->morphMany(Reviews::class,'reviewable');
    }

    public function averageRating()
{
    return round($this->reviews()->whereBetween('rating', [1, 5])->avg('rating'), 1);
}
}
