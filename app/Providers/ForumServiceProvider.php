<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ForumService;
use App\Services\FileUploadService;

class ForumServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ForumService::class, function ($app) {
            return new ForumService();
        });

        $this->app->singleton(FileUploadService::class, function ($app) {
            return new FileUploadService(
                config('filesystems.default'),
                config('forum.uploads.directory', 'forum/uploads')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/forum.php' => config_path('forum.php'),
        ], 'forum-config');

        // Register views
        $this->loadViewsFrom(__DIR__.'/../../resources/views/forum', 'forum');
        
        // Register routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/forum.php');
        
        // Register migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations/forum');
    }
}
