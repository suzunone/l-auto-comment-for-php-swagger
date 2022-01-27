<?php

/**
 * This file is part of auto-comment-for-l5-swagger
 *
 */

return [
    'documentations' => [
        'default' => [
            'session_cookie_name' => 'session_cookie_name',
            'ignored_models' => [],
            'ControllerName' => \App\Http\Controllers\L5Swagger\OpenApiDoc::class,
            'schema_path' => base_path('app/Schemas'),
            'schema_name_space' => base_path('App\Schemas'),
            'ignored_route_names' => [
                'l5-swagger.default.api'
            ],
            'enabled_route_names' => [
            ],
        ],
    ],
];
