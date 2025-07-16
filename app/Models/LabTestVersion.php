<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabTestVersion extends Model
{
    protected $fillable = ['test_id', 'file_path', 'saved_at'];

    public $timestamps = false;

    public function test()
    {
        return $this->belongsTo(LabTests::class, 'test_id');
    }
}
