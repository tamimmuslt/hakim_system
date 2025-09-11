<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
class Doctor extends Model
{
    protected $primaryKey='doctor_id';

    protected $fillable=[
      'name',
        'user_id',
        'specialty',
        'phone',
        'bio',
    'service_id'];

        public function user()
        {
            return $this->belongsTo(User::class,'user_id');
        }

        public function availability() 
         {
            return $this->hasmany(DoctorAvailability::class,'doctor_id'); 
        }

        public function appointments() 
         {
            return $this->hasMany(Appointments::class,'doctor_id');
        }

        public function prescriptions()
         {
            return $this->hasMany(Prescriptions::class,'doctor_id');
        }

        public function centers() 
         {
            return $this->belongsTomany(Centers::class,'center_doctors','doctor_id','center_id');
        }

        public function reviews() 
        {
            return $this->morphMany(Reviews::class,'reviewable');
        }

        public function averageRating()
{
    return round($this->reviews()->whereBetween('rating', [1, 5])->avg('rating'), 1);
}

 public function service()
{
    return $this->belongsTo(Services::class, 'service_id','service_id'); 
}
protected static function boot()
    {
        parent::boot();

        // عند إنشاء طبيب
        static::created(function ($doctor) {
            Log::info("📋 طبيب جديد تم إنشاؤه: " . $doctor->user->name);
        });

        // عند تعديل الطبيب
        static::updated(function ($doctor) {
            Log::info("✏️ تعديل بيانات طبيب: " . $doctor->user->name);
        });

        // عند حذف الطبيب
        static::deleted(function ($doctor) {
            Log::warning("❌ حذف طبيب: " . $doctor->user->name);
        });
    }


    public function reports()
{
    return $this->morphMany(Reports::class, 'reportable');
}

}
