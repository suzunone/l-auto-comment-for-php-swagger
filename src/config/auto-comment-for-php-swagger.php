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
            | Write Model relation count properties
            |--------------------------------------------------------------------------
            |
            | Set to false to disable writing of relation count properties to model DocBlocks.
            |
            */

            'write_model_relation_count_properties' => false,

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
            'always_using_session_cookie' => false,

            /*
              |--------------------------------------------------------------------------
              | Session CSRF Header
              |--------------------------------------------------------------------------
              |
              |
              */
            'always_using_csrf_header' => false,

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
            'controller_name' => \App\Http\Controllers\Swagger\OpenApiDoc::class,

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
                'l5-swagger.default.api',
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

            /*
             |--------------------------------------------------------------------------
             | Info class name
             |--------------------------------------------------------------------------
             |
             |
             */
            'info_class_name' => \App\Http\Controllers\Swagger\OpenApiInfo::class,

            /*
             |--------------------------------------------------------------------------
             | open api info settings
             |--------------------------------------------------------------------------
             |
             |
             */
            'open_api_info' => [
                'title' => env('APP_NAME', ''),
                'version' => '1.0.0',
                'termsOfService' => 'https://github.com/suzunone/l-auto-comment-for-php-swagger',
                'description' => <<<'DESCRIPTION'
# description
 * here is description
 
[l-auto-comment-for-php-swagger](https://github.com/suzunone/l-auto-comment-for-php-swagger)

DESCRIPTION,
                'contact' => [
                    'name' => null,
                    'url' => null,
                    'email' => null,
                ],
                'license' => [
                    'name' => 'MIT',
                    'url' => 'https://github.com/suzunone/l-auto-comment-for-php-swagger/blob/main/LICENSE',
                ],

                'tags' => [
                    'TagName' => [
                        'description' => 'Description',
                        'external_documentation' => [
                            'description' => 'Description',
                            'url' => 'http://swagger.io',
                        ],
                    ],
                ],
                'server' => [
                    [
                        'description' => 'Description',
                        'url' => 'https://virtserver.swaggerhub.com/swagger/Petstore/1.0.0',
                    ],
                ],
                'external_documentation' => [
                    'description' => 'Description',
                    'url' => 'http://swagger.io',
                ],
            ],

            /*
             |--------------------------------------------------------------------------
             | security scheme class name
             |--------------------------------------------------------------------------
             |
             |
             */
            'security_scheme_class_name' => \App\Http\Controllers\Swagger\SecuritySchemeOpenApiDoc::class,

            /*
             |--------------------------------------------------------------------------
             | security scheme settings
             |--------------------------------------------------------------------------
             |
             |
             */
            'security_schemes' => [
                'BasicAuth' => [
                    'enable' => true,
                    'type' => 'http',
                    'scheme' => 'basic',
                ],

                'BearerAuth' => [
                    'enable' => true,
                    'type' => 'http',
                    'scheme' => 'bearer',
                ],

                'ApiKeyAuth' => [
                    'enable' => true,
                    'type' => 'apiKey',
                    'in' => 'header',
                    'name' => 'X-API-Key',
                ],

                'OpenID' => [
                    'enable' => true,
                    'type' => 'openIdConnect',
                    'openIdConnectUrl' => 'https://example.com/.well-known/openid-configuration',
                ],

                'OAuth2' => [
                    'enable' => true,
                    'type' => 'oauth2',
                    'flows' => [
                        'flow' => 'implicit',
                        // 'flow' => 'password',
                        // 'flow' => 'clientCredentials',
                        // 'flow' => 'authorizationCode',
                        'authorizationUrl' => 'https://example.com/oauth/authorize',
                        'tokenUrl' => 'https://example.com/oauth/token',
                        'refreshUrl' => 'https://example.com/oauth/refresh',
                        'scopes' => [
                            'read' => 'Grants read access',
                            'write' => 'Grants write access',
                            'admin' => 'Grants access to admin operations',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
