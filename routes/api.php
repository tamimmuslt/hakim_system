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
use App\Http\Controllers\RadiologyImagesController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ServicesbookingController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PromotionsController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PatientController;




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    // إرسال أو إعادة إرسال كود التفعيل
    Route::post('/send-email-verification-code', [AuthController::class, 'sendEmailVerificationCode']);

    // التحقق من كود التفعيل
    Route::post('/verify-email-code', [AuthController::class, 'verifyEmailCode']);
});

Route::middleware(['auth:api', 'verifyEmailCode'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::get('/me', [AuthController::class, 'me']);


//userconttroller

    Route::get('/me', [UserController::class, 'profile']);

    Route::put('/me', [UserController::class, 'update']);

    Route::delete('/me', [UserController::class, 'destroy']);


    // // 👨‍⚕️ الأطباء
    //  Route::get('/doctors', [DoctorController::class, 'index']);
    // Route::post('/doctors', [DoctorController::class, 'store']); // فقط Center
    // Route::put('/doctors', [DoctorController::class, 'update']); // فقط Doctor نفسه
    // Route::delete('/doctors/{id}', [DoctorController::class, 'destroy']); // فقط Center المرتبط بالطبيب
    // Route::post('/doctors/{id}/approve', [DoctorController::class, 'approve']); // فقط Super_Admin

    // // 🏥 المراكز
    //    Route::get('/centers', [CentersController::class, 'index']);
    // Route::post('/centers', [CentersController::class, 'store']); // فقط Super_Admin
    // Route::delete('/centers/{id}', [CentersController::class, 'destroy']); // فقط Super_Admin
    // Route::post('/centers/{id}/approve', [CentersController::class, 'approve']); // فقط Super_Admin

    // Route::post('/centers/{center_id}/add-doctor', [CentersController::class, 'addDoctor']); // 

    Route::get('/patient', [PatientController::class, 'index']);        
    Route::get('/patient/{id}', [PatientController::class, 'show']);      
    Route::put('/patient/{id}', [PatientController::class, 'update']);    
    Route::delete('patient/{id}', [PatientController::class, 'destroy']);

    Route::get('/doctors/{doctor_id}/availability', [DoctorAvailabilityController::class, 'index']);
    Route::post('/availability', [DoctorAvailabilityController::class, 'store']);
    Route::put('/availability/{id}', [DoctorAvailabilityController::class, 'update']);
    Route::delete('/availability/{id}', [DoctorAvailabilityController::class, 'destroy']);

    Route::get('/appointments', [AppointmentsController::class, 'index']);
    Route::post('/appointments', [AppointmentsController::class, 'store']);
    Route::get('/appointments/{id}', [AppointmentsController::class, 'show']);
    Route::put('/appointments/{id}', [AppointmentsController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentsController::class, 'destroy']);

    Route::get('/records', [MedicalRecordsController::class, 'index']);
    Route::post('/records', [MedicalRecordsController::class, 'store']);
    Route::get('/records/{id}', [MedicalRecordsController::class, 'show']);
    Route::put('/records/{id}', [MedicalRecordsController::class, 'update']);
    Route::delete('/records/{id}', [MedicalRecordsController::class, 'destroy']);

    Route::get('/prescriptions', [PrescriptionsController::class, 'index']);
    Route::post('/prescriptions', [PrescriptionsController::class, 'store']);
    Route::get('/prescriptions/{id}', [PrescriptionsController::class, 'show']);
    Route::put('/prescriptions/{id}', [PrescriptionsController::class, 'update']);
    Route::delete('/prescriptions/{id}', [PrescriptionsController::class, 'destroy']);

    Route::get('/lab-tests', [LabTestsController::class, 'index']);
    Route::post('/lab-tests', [LabTestsController::class, 'store']);
    Route::get('/lab-tests/{id}', [LabTestsController::class, 'show']);
    Route::match(['put','post'],'/lab-tests/{id}', [LabTestsController::class, 'update']);
    Route::delete('/lab-tests/{id}', [LabTestsController::class, 'destroy']);

    Route::get('/radiology-images', action: [RadiologyImagesController::class, 'index']);
    Route::post('/radiology-images', [RadiologyImagesController::class, 'store']);
    Route::get('/radiology-images/{id}', [RadiologyImagesController::class, 'show']);
    Route::match(['put','post'],'/radiology-images/{id}', [RadiologyImagesController::class, 'update']);
    Route::delete('/radiology-images/{id}', [RadiologyImagesController::class, 'destroy']);

    // 🛎️ الخدمات
    Route::get('/services', [ServicesController::class, 'index']);
    Route::post('/services', [ServicesController::class, 'store']);
    Route::get('/services/{id}', [ServicesController::class, 'show']);
    Route::put('/services/{id}', [ServicesController::class, 'update']);
    Route::delete('/services/{id}', [ServicesController::class, 'destroy']);

Route::get('/services/{id}/check-type', [ServicesController::class, 'checkServiceType']);

    // 📅 حجوزات الخدمات    
    Route::get('/service-bookings', [ServicesbookingController::class, 'index']);
    Route::post('/service-bookings', [ServicesbookingController::class, 'store']);
    Route::get('/service-bookings/{id}', [ServicesbookingController::class, 'show']);
    Route::put('/service-bookings/{id}', [ServicesbookingController::class, 'update']);
    Route::delete('/service-bookings/{id}', [ServicesbookingController::class, 'destroy']);

    // 🔔 الإشعارات
    Route::get('/notifications', [NotificationsController::class, 'index']);
    Route::post('/notifications', [NotificationsController::class, 'store']);
    Route::get('/notifications/{id}', [NotificationsController::class, 'show']);
    Route::put('/notifications/{id}/mark-as-read', [NotificationsController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationsController::class, 'destroy']);

    // 🎁 العروض الترويجية
    Route::get('/promotions', [PromotionsController::class, 'index']);
    Route::post('/promotions', [PromotionsController::class, 'store']);
    Route::get('/promotions/{id}', [PromotionsController::class, 'show']);
    Route::put('/promotions/{id}', [PromotionsController::class, 'update']);
    Route::delete('/promotions/{id}', [PromotionsController::class, 'destroy']);

    // ⭐ التقييمات
    Route::get('/reviews', [ReviewsController::class, 'index']);
    Route::post('/reviews', [ReviewsController::class, 'store']);
    Route::get('/reviews/{id}', [ReviewsController::class, 'show']);
    Route::delete('/reviews/{id}', [ReviewsController::class, 'destroy']);

    // //صلاحيات الادمن
    //     // الأطباء
    // Route::get('/admin/doctors', [AdminController::class, 'doctors']);
    // Route::post('/admin/doctors/{id}/approve', [AdminController::class, 'approveDoctor']);

    // // المستخدمين
    // Route::get('/admin/users', [AdminController::class, 'users']);
    // Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);

    // // المراكز
    // Route::post('/admin/centers/{id}/approve', [AdminController::class, 'approveCenter']);
    // Route::delete('/admin/centers/{id}', [AdminController::class, 'deleteCenter']);

    // // العروض الترويجية
    // Route::post('/admin/promotions', [AdminController::class, 'addPromotion']);
    
});


Route::middleware(['auth:api'])->group(function () {

    Route::get('/admin/doctors', [AdminController::class, 'doctors']);

    Route::post('/admin/approve-doctor/{id}', [AdminController::class, 'approveDoctor']);

    Route::get('/admin/users', [AdminController::class, 'users']);

    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);

    Route::post('/admin/approve-center/{id}', [AdminController::class, 'approveCenter']);

    Route::delete('/admin/centers/{id}', [AdminController::class, 'deleteCenter']);

    Route::post('/admin/add-admin', [AdminController::class, 'addAdmin']);

    Route::post('/admin/add-center', [AdminController::class, 'addCenter']);

    Route::post('/admin/add-promotion', [AdminController::class, 'addPromotion']);

    Route::post('/admin/promotions/{id}/approve', [AdminController::class, 'approvePromotion']);

});


// centers
Route::get('/centers', [CentersController::class, 'index']);

Route::put('/centers/{id}', [CentersController::class, 'update'])->middleware('auth:api');

Route::delete('/centers/{id}', [CentersController::class, 'destroy'])->middleware('auth:api');

Route::post('/centers/{center_id}/add-doctor', [CentersController::class, 'addDoctor'])->middleware('auth:api');

Route::post('/centers/{id}/approve', [CentersController::class, 'approve'])->middleware('auth:api');

  Route::post('centers/{center_id}/doctors/create', [CentersController::class, 'createDoctor'])->middleware('auth:api');



//doctors
Route::get('/doctors', [DoctorController::class, 'index']);

Route::post('/doctors', [DoctorController::class, 'store'])->middleware('auth:api');

Route::put('/doctors/{id}', [DoctorController::class, 'update'])->middleware('auth:api');

Route::delete('/doctors/{id}', [DoctorController::class, 'destroy'])->middleware('auth:api');

Route::post('/doctors/{id}/approve', [DoctorController::class, 'approve'])->middleware('auth:api');


