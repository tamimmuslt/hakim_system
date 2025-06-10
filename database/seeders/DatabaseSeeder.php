<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Centers;
use App\Models\Services;
use App\Models\Appointments;
use App\Models\MedicalRecords;
use App\Models\Prescriptions;
use App\Models\LabTests;
use App\Models\RadiologyImages;
use App\Models\ServiceBookings;
use App\Models\Reviews;
use App\Models\Notifications;
use App\Models\Promotions;
use App\Models\DoctorAvailability;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
//         User::truncate();
// Doctor::truncate();
// Centers::truncate();
// Services::truncate();
// Appointments::truncate();
// MedicalRecords::truncate();
// Prescriptions::truncate();
// LabTests::truncate();
// RadiologyImages::truncate();
// ServiceBookings::truncate();
// Reviews::truncate();
// Notifications::truncate();
// Promotions::truncate();
// DoctorAvailability::truncate();
        // Users
        User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => Hash::make('123456'), 'user_type' => 'Super_Admin']);
        User::create(['name' => 'Dr. rasheed', 'email' => 'dr@test.com', 'password' => Hash::make('333333'), 'user_type' => 'doctor']);
        User::create(['name' => 'Patient belal', 'email' => 'patient@test.com', 'password' => Hash::make('0000111'), 'user_type' => 'patient']);
        User::create(['name' => 'مركز الحكمة', 'email' => 'hakma@test.com', 'password' => Hash::make('0001111'), 'user_type' => 'center']);

        Doctor::create(['user_id' => 2, 'specialty' => 'عظمية', 'phone' => '0991112222']);

        // Centers
        $center=Centers::create(['user_id' => 4, 'address' => 'الحسكة-شارع المحطة',  'phone' => '052227997', 'type' => 'عام',    'latitude' => 31.9554,
    'longitude' => 35.9456,]);

        // Services
        $service=Services::create(['name' => 'تحاليل دم', 'description' => 'تحاليل مخبرية شاملة']);

        $center->services()->attach([ $service->service_id => ['price' => 20]]);

        // Appointments
        Appointments::create(['user_id' => 3, 'doctor_id' => 1, 'appointment_datetime' => now()->addDays(1), 'status' => 'scheduled', 'notes' => 'مراجعة دورية']);

        // Medical Records
        MedicalRecords::create(['user_id' => 3, 'appointment_id' => 1, 'diagnosis' => 'حرارة', 'start_date' => now(), 'end_date' => now()->addDays(5)]);

        // Prescription
        Prescriptions::create(['record_id' => 1, 'doctor_id' => 1, 'medication_name' => 'باراسيتامول', 'dosage' => '500mg', 'instructions' => 'ثلاث مرات يوميًا']);

        // Lab Test
        LabTests::create(['record_id' => 1, 'uploaded_by' => 2, 'test_name' => 'تحليل دم شامل', 'result' => 'سليم', 'test_date' => now()]);

        // Radiology Image
        RadiologyImages::create(['record_id' => 1, 'uploaded_by' => 2, 'image_url' => 'http://example.com/image.jpg', 'description' => 'صورة أشعة للصدر']);

        // Service Booking
        ServiceBookings::create(['user_id' => 3, 'service_id' => 1, 'booking_datetime' => now()->addDays(2), 'status' => 'confirmed']);

        // Review
        Reviews::create(['user_id' => 3, 'reviewable_type' => 'App\\Models\\Doctor', 'reviewable_id' => 1, 'rating' => 5, 'comment' => 'دكتور ممتاز']);

        // Notification
        Notifications::create(['user_id' => 3, 'message_text' => 'لديك موعد جديد غدًا', 'is_read' => false]);

        // Promotion
        Promotions::create(['center_id' => 1, 'title' => 'خصم على التحاليل', 'description' => 'خصم 30% على كافة التحاليل', 'start_date' => now(), 'end_date' => now()->addWeek(), 'discount_percent' => 30, 'price_after_discount' => 70, 'is_active' => true]);

        // Doctor Availability
        DoctorAvailability::create(['doctor_id' => 1, 'day_of_week' => 'friday', 'start_time' => '09:00', 'end_time' => '12:00']);
    }
}
