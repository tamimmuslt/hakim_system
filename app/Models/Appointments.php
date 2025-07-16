<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
    protected $primaryKey='appointment_id';

    protected $fillable =
    [
 'user_id',
  'doctor_id', 
  'service_id',
  'appointment_datetime',
   'status', 
   'notes'
    ];

    public function user() 
     {
        return $this->belongsTo(User::class,'user_id');     
    }

    public function doctor() 
     {
        return $this->belongsTo(Doctor::class,'doctor_id');     
    }

    public function record() 
    {
        return $this->hasone(MedicalRecords::class,'appointment_id');
    }
    public function service()
{
    return $this->belongsTo(Services::class);
}
}
