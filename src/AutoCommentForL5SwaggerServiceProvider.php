<?php

/**
 * This file is part of auto-comment-for-l5-swagger
 *
 */

namespace AutoCommentForL5Swagger;

use AutoCommentForL5Swagger\Commands\L5SwaggerComment;
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
        //Register commands
        $this->commands([L5SwaggerComment::class]);
        $this->commands([ModelToOpenApiSchema::class]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}
