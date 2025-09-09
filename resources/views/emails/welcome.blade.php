@component('mail::message')
<div style="text-align: center;">
    <img src="{{ $logo }}" alt="{{ $appName }}" style="max-width: 150px; margin-bottom:20px;">
</div>

# 👋 أهلًا {{ $user->name }}

مرحبًا بك في **{{ $appName }}**! 🎉  
نحن سعداء بانضمامك إلى منصتنا الطبية 🏥

@if($user->user_type === 'patient' && $code)
## رمز التفعيل الخاص بك:
🔐 **{{ $code }}**  
يرجى استخدام هذا الرمز لتفعيل بريدك الإلكتروني.
@endif

نتمنى لك تجربة صحية مميزة معنا ❤️

تحياتنا،  
فريق **{{ $appName }}**
@endcomponent
