<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Doctor;
use App\Models\CenterDoctor;
use App\Models\DoctorAvailability;
use App\Models\Appointment;
use App\Models\Appointments;
use App\Models\Center_Doctor;
use App\Models\Centers;
use App\Models\MedicalRecords;
use App\Models\Prescription;
use App\Models\LabTest;
use App\Models\LabTests;
use App\Models\RadiologyImage;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\Notification;
use App\Models\ServiceBooking;
use App\Models\LabTestVersion;
use App\Models\Notifications;
use App\Models\Prescriptions;
use App\Models\Promotions;
use App\Models\RadiologyImages;
use App\Models\RadiologyImageVersion;
use App\Models\Reviews;
use App\Models\ServiceBookings;
use App\Models\Services;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Users (مرضى، أطباء، مراكز، أدمن)
        $admin = User::create([
            'name' => 'الأدمن الرئيسي',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'Super_Admin',
            'email_verified_at' => now()
        ]);

        $patient1 = User::create([
            'name' => 'أحمد علي',
            'email' => 'ahmad@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'patient',
            'email_verified_at' => now()
        ]);

        $patient2 = User::create([
            'name' => 'سارة محمد',
            'email' => 'sara@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'patient',
            'email_verified_at' => now()
        ]);

        $doctorUser1 = User::create([
            'name' => 'د. محمد حسن',
            'email' => 'doctor1@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'Doctor',
            'email_verified_at' => now()
        ]);

        $doctorUser2 = User::create([
            'name' => 'د. ليلى أحمد',
            'email' => 'doctor2@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'Doctor',
            'email_verified_at' => now()
        ]);

        $centerUser = User::create([
            'name' => 'مركز الشفاء الطبي',
            'email' => 'center@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'Center',
            'email_verified_at' => now()
        ]);

        // 2️⃣ Center
        $center = Centers::create([
            'user_id' => $centerUser->user_id,
            'address' => 'شارع النيل، القاهرة',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'phone' => '0123456789',
            'type' => 'مستشفى'
        ]);

        // 3️⃣ Services
        $service1 = Services::create([
            'name' => 'تحاليل الدم',
            'description' => 'خدمة تحليل الدم الكامل'
        ]);
        $service2 = Services::create([
            'name' => 'أشعة سينية',
            'description' => 'خدمة الأشعة السينية للصدر'
        ]);

        // 4️⃣ Doctors
        $doctor1 = Doctor::create([
            'user_id' => $doctorUser1->user_id,
            'specialty' => 'أمراض باطنية',
            'phone' => '01011112222'
        ]);

        $doctor2 = Doctor::create([
            'user_id' => $doctorUser2->user_id,
            'specialty' => 'جراحة عامة',
            'phone' => '01033334444'
        ]);

        // 5️⃣ CenterDoctors
        Center_Doctor::create([
            'doctor_id' => $doctor1->doctor_id,
            'center_id' => $center->center_id
        ]);

        Center_Doctor::create([
            'doctor_id' => $doctor2->doctor_id,
            'center_id' => $center->center_id
        ]);

        // 6️⃣ DoctorAvailability
        DoctorAvailability::create([
            'doctor_id' => $doctor1->doctor_id,
            'day_of_week' => 'sunday',
            'start_time' => '09:00:00',
            'end_time' => '14:00:00'
        ]);

        DoctorAvailability::create([
            'doctor_id' => $doctor2->doctor_id,
            'day_of_week' => 'monday',
            'start_time' => '10:00:00',
            'end_time' => '15:00:00'
        ]);

        // 7️⃣ Appointments
        $appointment1 = Appointments::create([
            'user_id' => $patient1->user_id,
            'doctor_id' => $doctor1->doctor_id,
            'service_id' => $service1->service_id,
            'appointment_datetime' => now()->addDays(1),
            'status' => 'scheduled',
            'notes' => 'ملاحظات مهمة'
        ]);

        $appointment2 = Appointments::create([
            'user_id' => $patient2->user_id,
            'doctor_id' => $doctor2->doctor_id,
            'service_id' => $service2->service_id,
            'appointment_datetime' => now()->addDays(2),
            'status' => 'scheduled',
            'notes' => 'الرجاء الحضور قبل الموعد 10 دقائق'
        ]);

        // 8️⃣ MedicalRecords
        $record1 = MedicalRecords::create([
            'user_id' => $patient1->user_id,
            'appointment_id' => $appointment1->appointment_id,
            'diagnosis' => 'ارتفاع ضغط الدم',
            'treatment_plan' => 'اتباع نظام غذائي صحي وممارسة الرياضة',
            'progress_notes' => 'تحسن بسيط',
            'start_date' => now(),
            'end_date' => now()->addWeek()
        ]);

        $record2 = MedicalRecords::create([
            'user_id' => $patient2->user_id,
            'appointment_id' => $appointment2->appointment_id,
            'diagnosis' => 'كسر في اليد اليمنى',
            'treatment_plan' => 'جبيرة لمدة 6 أسابيع',
            'progress_notes' => 'محتاج متابعة أسبوعية',
            'start_date' => now(),
            'end_date' => now()->addWeeks(6)
        ]);

        // 9️⃣ Prescriptions
        Prescriptions::create([
            'record_id' => $record1->record_id,
            'doctor_id' => $doctor1->doctor_id,
            'medication_name' => 'دواء خافض للضغط',
            'dosage' => '1 حبة يومياً',
            'instructions' => 'بعد الإفطار'
        ]);

        Prescriptions::create([
            'record_id' => $record2->record_id,
            'doctor_id' => $doctor2->doctor_id,
            'medication_name' => 'مسكن ألم',
            'dosage' => '2 حبة يومياً',
            'instructions' => 'بعد الطعام'
        ]);

        // 1️⃣0️⃣ LabTests
        LabTests::create([
            'record_id' => $record1->record_id,
            'uploaded_by' => $doctor1->user_id,
            'test_name' => 'تحليل دم كامل',
            'result' => 'طبيعي',
            'test_date' => now()->subDay()
        ]);

        LabTests::create([
            'record_id' => $record2->record_id,
            'uploaded_by' => $doctor2->user_id,
            'test_name' => 'أشعة سينية',
            'result' => 'اليد مكسورة',
            'test_date' => now()->subDay()
        ]);

        // 1️⃣1️⃣ RadiologyImages
        RadiologyImages::create([
            'record_id' => $record2->record_id,
            'uploaded_by' => $doctor2->user_id,
            'image_url' => 'https://example.com/images/hand1.jpg',
            'description' => 'صورة اليد المكسورة'
        ]);

        // 1️⃣2️⃣ Promotions
        Promotions::create([
            'center_id' => $center->center_id,
            'title' => 'عرض الصيف',
            'description' => 'خصم 20% على جميع الخدمات',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'discount_percent' => 20,
            'price_after_discount' => 80,
            'is_active' => true
        ]);

        // 1️⃣3️⃣ Reviews
        Reviews::create([
            'user_id' => $patient1->user_id,
            'reviewable_type' => Doctor::class,
            'reviewable_id' => $doctor1->doctor_id,
            'rating' => 5,
            'comment' => 'دكتور ممتاز وخبرة عالية'
        ]);

        Reviews::create([
            'user_id' => $patient2->user_id,
            'reviewable_type' => Services::class,
            'reviewable_id' => $service2->service_id,
            'rating' => 4,
            'comment' => 'خدمة سريعة وفعالة'
        ]);

        // 1️⃣4️⃣ Notifications
        Notifications::create([
            'user_id' => $patient1->user_id,
            'message_text' => 'مرحباً أحمد، تم تسجيل موعدك بنجاح!',
            'is_read' => false
        ]);

        Notifications::create([
            'user_id' => $patient2->user_id,
            'message_text' => 'مرحباً سارة، تم تسجيل موعدك بنجاح!',
            'is_read' => false
        ]);

        // 1️⃣5️⃣ ServiceBookings
        ServiceBookings::create([
            'user_id' => $patient1->user_id,
            'service_id' => $service1->service_id,
            'booking_datetime' => now()->addDays(1),
            'status' => 'pending',
            'notes' => 'ملاحظة: يرجى إحضار نتائج التحاليل السابقة'
        ]);

        ServiceBookings::create([
            'user_id' => $patient2->user_id,
            'service_id' => $service2->service_id,
            'booking_datetime' => now()->addDays(2),
            'status' => 'pending',
            'notes' => 'ملاحظة: يجب الحضور قبل الموعد 15 دقيقة'
        ]);

        // 1️⃣6️⃣ LabTestVersions
        foreach(LabTests::all() as $test) {
            LabTestVersion::create([
                'test_id' => $test->test_id,
                'file_path' => '/files/labtest_'.$test->test_id.'.pdf',
                'saved_at' => now()
            ]);
        }

        // 1️⃣7️⃣ RadiologyImageVersions
        foreach(RadiologyImages::all() as $image) {
            RadiologyImageVersion::create([
                'radiology_image_id' => $image->image_id,
                'image_url' => $image->image_url,
                'saved_at' => now()
            ]);
        }

    }
}
