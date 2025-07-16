<?php
// app/Models/RadiologyImageVersion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadiologyImageVersion extends Model
{
    protected $fillable = ['radiology_image_id', 'image_url', 'saved_at'];

    public function image()
    {
        return $this->belongsTo(RadiologyImages::class, 'radiology_image_id');
    }
}
