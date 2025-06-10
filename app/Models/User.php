<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable,HasApiTokens;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'phone',
        'birthdate',
        'weight',
        'blood_type',
        'user_type',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // علاقات المستخدم
    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    public function center()
{
    return $this->hasOne(Centers::class, 'user_id');
}
    public function record()
    {
        return $this->hasOne(MedicalRecords::class, 'user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notifications::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Reviews::class, 'user_id');
    }

    public function uploadedRadiologyImages()
    {
        return $this->hasMany(RadiologyImages::class, 'user_id');
    }

    public function uploadedLabTests()
    {
        return $this->hasMany(LabTests::class, 'user_id');
    }

    public function serviceBookings()
    {
        return $this->hasMany(ServiceBookings::class, 'user_id');
    }

    
    public function getJWTIdentifier()
    {
        return $this->getKey(); 
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}