<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecords extends Model
{
    protected $primaryKey = 'record_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable=
    [
        'user_id',
         'appointment_id',
          'diagnosis',
        'treatment_plan', 
        'progress_notes',
         'start_date', 
        'end_date'

    ];

    public function user() 
     {
        return $this->belongsTo(User::class,'user_id');
    }

    public function appointment() 
     {
        return $this->belongsTo(Appointments::class,'appointment_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescriptions::class, 'record_id');
    }

    public function radiologyImages()
    {
        return $this->hasMany(RadiologyImages::class, 'record_id');
    }

    public function labTests()
    {
        return $this->hasMany(LabTests::class, 'record_id');
    }
}
