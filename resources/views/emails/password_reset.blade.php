@component('mail::message')
# مرحباً {{ $user->name }}

تم إعادة تعيين كلمة المرور الخاصة بك بنجاح في تطبيق **{{ $appName }}** ✅

@if($logoUrl)
<img src="{{ $logoUrl }}" alt="Logo" style="max-width: 150px; margin-top:15px;">
@endif

> إذا لم تقم بطلب إعادة التعيين، يرجى التواصل مع الدعم فوراً.

شكراً لك،  
فريق {{ $appName }}
@endcomponent
