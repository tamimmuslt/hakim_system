<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorAvailability extends Model
{
protected $primaryKey = 'availabilitie_id';
    protected $fillable=
    [
         'doctor_id',
          'day_of_week', 
          'start_time', 
          'end_time'
    ];

    public function doctor() 
     {
        return $this->belongsTo(Doctor::class,'doctor_id');
    }
}
