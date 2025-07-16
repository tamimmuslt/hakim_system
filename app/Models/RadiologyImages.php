<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadiologyImages extends Model
{
    protected $primaryKey='image_id';

    protected $fillable=
    [
        'record_id',
         'uploaded_by', 
         'image_url', 
         'description'
    ];

    public function record() 
     {
        return $this->belongsTo(MedicalRecords::class,'record_id');
    
    }

    public function uploader() 
     {
        return $this->belongsTo(User::class,'uploaded_by');
    }
    public function versions()
{
    return $this->hasMany(RadiologyImageVersion::class, 'radiology_image_id');
}
}
