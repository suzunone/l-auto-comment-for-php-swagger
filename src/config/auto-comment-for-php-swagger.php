<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

use Illuminate\Support\Str;

return [
    'documentations' => [
        'default' => [
            /*
            |--------------------------------------------------------------------------
            | Model locations to include
            |--------------------------------------------------------------------------
            |
            | Define in which directories the ide-helper:models command should look
            | for models.
            |
            | glob patterns are supported to easier reach models in sub-directories,
            | e.g. `app/Services/* /Models` (without the space)
            |
            */
            'model_locations' => [
                'app',
            ],

            /*
            |--------------------------------------------------------------------------
            | Schemas filename
            |--------------------------------------------------------------------------
            |
            | The default filename for the coherent Schema file.
            | If null, it is the same as the Model name.
            |
            */
            'schemas_filename' => null,

            /*
            |--------------------------------------------------------------------------
            | Session Cookie Name
            |--------------------------------------------------------------------------
            |
            |
            */
            'session_cookie_name' => env(
                'SESSION_COOKIE',
                Str::slug(env('APP_NAME', 'laravel'), '_') . '_session'
            ),

            /*
            |--------------------------------------------------------------------------
            | Skip to create open api Schema file
            |--------------------------------------------------------------------------
            |
            | Define which models should be ignored.
            |
            */
            'ignored_models' => [],

            /*
            |--------------------------------------------------------------------------
            | Api Example Property
            |--------------------------------------------------------------------------
            |
            |
            */
            'api_example_property_name' => 'api_example',

            /*
            |--------------------------------------------------------------------------
            | Controller for outputting doc comment
            |--------------------------------------------------------------------------
            |
            |
            */
            'ControllerName' => \App\Http\Controllers\Swagger\OpenApiDoc::class,

            /*
            |--------------------------------------------------------------------------
            | Where to save the open api Schema file
            |--------------------------------------------------------------------------
            |
            |
            */
            'schema_path' => base_path('app/Schemas'),

            /*
            |--------------------------------------------------------------------------
            | Namespace of open api Schema file
            |--------------------------------------------------------------------------
            |
            |
            */
            'schema_name_space' => 'App\Schemas',

            /*
            |--------------------------------------------------------------------------
            | Routing not to output to doc comment
            |--------------------------------------------------------------------------
            |
            |
            */
            'ignored_route_names' => [
                'l5-swagger.default.api'
            ],

            /*
            |--------------------------------------------------------------------------
            | Routing to be output to doc comments
            |--------------------------------------------------------------------------
            |
            |
            */
            'enabled_route_names' => [
            ],

            /*
             |--------------------------------------------------------------------------
             | swagger json to add
             |--------------------------------------------------------------------------
             |
             |
             */
            'swagger_json_files' => [
            ],

            /*
             |--------------------------------------------------------------------------
             | swagger YAML to add
             |--------------------------------------------------------------------------
             |
             |
             */
            'swagger_yaml_files' => [
            ],
        ],
    ],
];
