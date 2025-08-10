<?php

namespace App\Providers;

use App\Repositories\ImageUploadRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register ImageUploadService as singleton
        $this->app->singleton(ImageUploadRepository::class, function ($app) {
            return new ImageUploadRepository();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set maximum image dimensions if not set in php.ini
        if (ini_get('memory_limit') !== '-1') {
            ini_set('memory_limit', '256M');
        }

        // Increase execution time for image processing
        ini_set('max_execution_time', '300');

        // Set upload limits if not configured
        if (ini_get('upload_max_filesize') === '2M') {
            ini_set('upload_max_filesize', '2M');
            ini_set('post_max_size', '5M');
        }

        DB::listen(function ($query) {
        if ($query->time > 1000) { // Query lebih dari 1 detik
            Log::info('Slow Query: ' . $query->time . 'ms');
            Log::info('SQL: ' . $query->sql);
            Log::info('Bindings: ' . json_encode($query->bindings));
        }
    });
    }
}
