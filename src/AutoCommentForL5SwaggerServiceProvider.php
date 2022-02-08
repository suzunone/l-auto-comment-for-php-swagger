<?php

/**
 * This file is part of auto-comment-for-l5-swagger
 *
 */

namespace AutoCommentForL5Swagger;

use AutoCommentForL5Swagger\Commands\FileToAnnotationComment;
use AutoCommentForL5Swagger\Commands\ModelToOpenApiSchema;
use Illuminate\Support\ServiceProvider;

class AutoCommentForL5SwaggerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/config/auto-comment-for-l5-swagger.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('auto-comment-for-l5-swagger.php');
        } else {
            $publishPath = base_path('config/auto-comment-for-l5-swagger.php');
        }

        $this->publishes([$configPath => $publishPath], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/config/auto-comment-for-l5-swagger.php';

        // merge default config
        $this->mergeConfigFrom(
            $configPath,
            'auto-comment-for-l5-swagger'
        );

        //Register commands
        $this->commands([FileToAnnotationComment::class, ModelToOpenApiSchema::class, FileToAnnotationComment::class]);
    }
}
