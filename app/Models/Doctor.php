<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $primaryKey='doctor_id';

    protected $fillable=[
        'user_id',
        'specialty'
        ,'phone'];

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

}
