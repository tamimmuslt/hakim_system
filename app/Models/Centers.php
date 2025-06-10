<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Centers extends Model
{
    protected $primaryKey ='center_id';

    protected $fillable=
    [
        
         'user_id',
         'address',
         'type',
         'phone',
          'latitude',
          'longitude'
    ];

    public function doctors()
     {
        return $this->belongsToMany(Doctor::class,'center_doctors','center_id','doctor_id');
    }

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

    public function services() 
     {
        return $this->belongsToMany(Services::class,'center_services','center_id','service_id')->withPivot('price')->withTimestamps();
    }
     
    public function promotions()
     {
        return $this->hasMany(Promotions::class,'center_id');
    }

    public function reviews()
    {
        return $this->morphMany(Reviews::class,'reviwable');
    }

    public function averageRating()
{
    return round($this->reviews()->whereBetween('rating', [1, 5])->avg('rating'), 1);
}
}
