<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger;

use AutoCommentForPHPSwagger\Commands\FileToAnnotationComment;
use AutoCommentForPHPSwagger\Commands\ModelToOpenApiSchema;
use AutoCommentForPHPSwagger\Commands\SwaggerComment;
use Illuminate\Support\ServiceProvider;

class LAutoCommentForPHPSwaggerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/config/auto-comment-for-php-swagger.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('auto-comment-for-php-swagger.php');
        } else {
            $publishPath = base_path('config/auto-comment-for-php-swagger.php');
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
        $configPath = __DIR__ . '/config/auto-comment-for-php-swagger.php';

        // merge default config
        $this->mergeConfigFrom(
            $configPath,
            'auto-comment-for-php-swagger'
        );

        //Register commands
        $this->commands([FileToAnnotationComment::class, ModelToOpenApiSchema::class, SwaggerComment::class]);
    }
}
