<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabTests extends Model
{
    protected $primaryKey = 'test_id';

    protected $fillable = 
    [
        'record_id',
     'uploaded_by', 
     'test_name',
      'result', 
      'test_date'
    ];

    public function record()
    {
        return $this->belongsTo(MedicalRecords::class, 'record_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
