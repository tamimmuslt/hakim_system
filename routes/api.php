<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\CentersController;
use App\Http\Controllers\DoctorAvailabilityController;
use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\MedicalRecordsController;
use App\Http\Controllers\PrescriptionsController;
use App\Http\Controllers\LabTestsController;

// مسارات تسجيل الدخول والتسجيل
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// مسارات محمية بالتوكن
Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [UserController::class, 'profile']);
    Route::put('/me', [UserController::class, 'update']);
    Route::delete('/me', [UserController::class, 'destroy']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('/doctors', [DoctorController::class, 'index']);        // عرض كل الأطباء
    Route::get('/doctors/{id}', [DoctorController::class, 'show']);    // عرض طبيب واحد
    Route::post('/doctors', [DoctorController::class, 'store']);       // إنشاء طبيب جديد
    Route::put('/doctors/{id}', [DoctorController::class, 'update']);  // تعديل طبيب
    Route::delete('/doctors/{id}', [DoctorController::class, 'destroy']); // حذف طبيب
});

Route::middleware('auth:api')->group(function () {
Route::get('/centers', [CentersController::class, 'index']);
Route::get('/centers/{id}', [CentersController::class, 'show']);
Route::put('/doctors/{id}/approve', [DoctorController::class, 'approve']);
Route::post('/centers', [CentersController::class, 'store']);
Route::put('/centers/{id}', [CentersController::class, 'update']);
Route::delete('/centers/{id}', [CentersController::class, 'destroy']);

// البحث عن أقرب مركز حسب الإحداثيات
Route::get('/centers/nearby', [CentersController::class, 'nearbyCenters']);
});



Route::middleware('auth:api')->group(function () {
    Route::get('/doctors/{doctor_id}/availability', [DoctorAvailabilityController::class, 'index']);
    Route::post('/availability', [DoctorAvailabilityController::class, 'store']);
    Route::put('/availability/{id}', [DoctorAvailabilityController::class, 'update']);
    Route::delete('/availability/{id}', [DoctorAvailabilityController::class, 'destroy']);
});



Route::middleware('auth:api')->group(function () {
    Route::get('/appointments', [AppointmentsController::class, 'index']);
    Route::post('/appointments', [AppointmentsController::class, 'store']);
    Route::get('/appointments/{id}', [AppointmentsController::class, 'show']);
    Route::put('/appointments/{id}', [AppointmentsController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentsController::class, 'destroy']);
});



Route::middleware('auth:api')->group(function () {
    Route::get('/records', [MedicalRecordsController::class, 'index']);
    Route::post('/records', [MedicalRecordsController::class, 'store']);
    Route::get('/records/{id}', [MedicalRecordsController::class, 'show']);
    Route::put('/records/{id}', [MedicalRecordsController::class, 'update']);
    Route::delete('/records/{id}', [MedicalRecordsController::class, 'destroy']);
});


Route::middleware('auth:api')->group(function () {
    Route::get('/prescriptions', [PrescriptionsController::class, 'index']);
    Route::post('/prescriptions', [PrescriptionsController::class, 'store']);
    Route::get('/prescriptions/{id}', [PrescriptionsController::class, 'show']);
    Route::put('/prescriptions/{id}', [PrescriptionsController::class, 'update']);
    Route::delete('/prescriptions/{id}', [PrescriptionsController::class, 'destroy']);
});


Route::middleware('auth:api')->group(function () {
    Route::get('/lab-tests', [LabTestsController::class, 'index']);
    Route::post('/lab-tests', [LabTestsController::class, 'store']);
    Route::get('/lab-tests/{id}', [LabTestsController::class, 'show']);
    Route::put('/lab-tests/{id}', [LabTestsController::class, 'update']);
    Route::delete('/lab-tests/{id}', [LabTestsController::class, 'destroy']);
});