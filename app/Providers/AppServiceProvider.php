<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Added for Azure Web App deployment https://chatgpt.com/c/6941ba71-d628-8321-a010-e2ec69b400c1
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Limit to 2 requests per second per website
        // RateLimiter::for('external-crawler', function (object $job) {
        //     // We rate limit based on the website ID so we can crawl 
        //     // different websites simultaneously, but not one site too fast.
        //     return Limit::perSecond(2)->by($job->websiteId ?? 'default');
        // });
    }
}
