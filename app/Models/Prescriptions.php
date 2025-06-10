<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescriptions extends Model
{
    protected $primaryKey='prescription_id';

    protected $fillable=
    [
        'record_id', 
        'doctor_id', 
        'medication_name',
         'dosage', 
         'instructions'
    ];

    public function record() 
     {
        return $this->belongsTo(MedicalRecords::class,'record_id');
    }

    public function doctor()
     {
        return $this->belongsTo(Doctor::class,'doctor_id');
    }
}
