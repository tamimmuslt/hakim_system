<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ✅ تأكد أن signed URLs تعمل بشكل صحيح (مثلاً روابط تحقق البريد)
        URL::forceRootUrl(config('app.url'));

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
    }
}
