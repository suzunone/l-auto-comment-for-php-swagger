<?php

/**
 * This file is part of auto-comment-for-l5-swagger
 *
 */

namespace AutoCommentForL5Swagger\Commands;

use App\Http\Controllers\L5Swagger\OpenApiDoc;
use function collect;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use function optional;
use Str;

class L5SwaggerComment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'openapi:l5-swagger-comment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $router;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Router $Router)
    {
        // $this->call('make:controller', ['name' => 'L5Swagger/OpenApiDoc']);
        if (!class_exists(OpenApiDoc::class)) {
            $this->comment('please call php artisan make:controller L5Swagger/OpenApiDoc');
            return -1;
        }

        $this->router = $Router;
        $route = $this->getRoutes();

        $comment = <<<'COMMENT'
/**

COMMENT;

        foreach ($route as $route_item) {
            foreach ($route_item['methods'] as $method) {
                $comment .= $this->getL5Comment($method, $route_item);
            }
        }

        $comment .= ' */';

        $stub = file_get_contents(__DIR__ . '/stubs/lg_swagger_controller.stub');

        $stub = str_replace('// OpenApiDoc //', $comment, $stub);

        file_put_contents((new \ReflectionClass(OpenApiDoc::class))->getFileName(), $stub);
    }

    public function getL5Comment($method, $route_item)
    {
        $method = \Str::studly(strtolower($method));

        $anntation = $this->parseAnnotation($route_item['doc_comment']);
        if (!count($anntation)) {
            return '';
        }

        $tags = [Str::studly(explode('.', $route_item['name'])[0])];
        if (isset($anntation['openapi-tags'])) {
            $tags = [];
            foreach ($anntation['openapi-tags'] as $item) {
                $tags = array_merge($tags, $item);
            }
        }

        $tags = implode('","', $tags);

        $description = $this->parseDescriotion($route_item['doc_comment']);

        $operationId = strtolower($method) . Str::studly(str_replace('.', '-', $route_item['name']));

        $comment = <<<COMMENT
 * @OA\\{$method}(
 *     tags={"{$tags}"},
 *     operationId="{$operationId}",
 *     path="/{$route_item['uri']}",
 *     description="{$description}",
 *

COMMENT;

        if (!isset($anntation['openapi-ignore-cookie'])) {
            $cookie_name = $anntation['openapi-cookie'][0][0] ?? $this->laravel['config']->get('auto-comment-for-l5-swagger.session_cookie_name', 'session_cookie');
            $comment .= $this->getCookie($cookie_name, isset($anntation['openapi-ignore-session-cookie']), isset($anntation['openapi-ignore-csrf-cookie']));
        }

        foreach ($route_item['attributes'] as $attribute) {
            if (strpos($attribute['type'], 'App\\Http\\Requests') !== 0) {
                continue;
            }
            $comment .= $this->formRequestToComment($attribute['type']);
        }

        $comment .= $this->makePathOption($route_item['uri']);
        $comment .= $this->makeResponseTag($anntation);
        $comment .= <<<'COMMENT'
 * )
 *

COMMENT;

        return $comment;
    }

    public function makePathOption($url)
    {
        preg_match_all('/\{([^}]*)\}/u', $url, $match);
        $comment = '';
        foreach ($match[1] as $path) {
            $comment .= <<<COMMENT
 *     @OA\\Parameter(
 *         name="{$path}",
 *         description="{$path}_id",
 *         @OA\\Schema(
 *             format="int64",
 *             type="integer"
 *         ),
 *         in="path",
 *         required=true
 *     ),

COMMENT;
        }

        return $comment;
    }

    public function makeResponseTag($anntation)
    {
        $comment = '';
        foreach ($anntation['openapi-response'] as $os_res) {
            $response = array_shift($os_res);
            $ref = array_shift($os_res);
            $description = implode(' ', $os_res);
            $comment .= <<<COMMENT
 *     @OA\\Response(
 *         response="{$response}",
 *         description="{$description}",
 *         @OA\\JsonContent(ref="{$ref}")
 *     ),

COMMENT;
        }

        return $comment;
    }

    public function parseDescriotion($doc_comment)
    {
        preg_match_all('/^\/\*\*([^@]+)/u', $doc_comment, $match);

        $match = $match[1][0] ?? '';

        $match = mb_ereg_replace('\n +\* *', '', $match);
        $match = mb_ereg_replace('\n', ' ', $match);

        return trim($match);
    }

    public function parseAnnotation($doc_comment)
    {
        $doc_comment = mb_ereg_replace(' +', ' ', $doc_comment);

        preg_match_all('/ \* @(openapi[^ \n]*)( +[^\n]*)?\n/u', $doc_comment, $match);

        $res = [
        ];

        foreach ($match[1] as $key => $annotation) {
            $res[$annotation][] = explode(' ', trim($match[2][$key]) ?? '');
        }

        return $res;
    }

    public function formRequestToComment($class_name)
    {
        if (!class_exists($class_name)) {
            $this->error($class_name . 'is not found');
            return;
        }

        $reflection = new \ReflectionClass($class_name);

        preg_match_all('/@property(-read)? +([^ ]+) +[$]?([^ ]+) +(.*)/', $reflection->getDocComment(), $match);

        $comment = '';
        foreach ($match[0] as $key => $value) {
            $required = strpos($match[2][$key], 'null') !== false ? 'false' : 'true';
            $format = 'format="any"';
            if (strpos($match[2][$key], 'int') !== false) {
                $format = 'format="int64",type="integer"';
            } elseif (strpos($match[2][$key], 'bool') !== false) {
                $format = 'format="boolean"';
            } elseif (strpos($match[2][$key], 'boolean') !== false) {
                $format = 'format="boolean"';
            } elseif (strpos($match[2][$key], 'string') !== false) {
                $format = 'format="string"';
            }

            $comment .= <<<COMMENT
 *     @OA\\Parameter(
 *         name="{$match[3][$key]}",
 *         description="{$match[4][$key]}",
 *         @OA\\Schema(
 *             {$format}
 *         ),
 *         in="query",
 *         required={$required}
 *     ),

COMMENT;
        }

        return $comment;
    }

    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes()
    {
        return collect($this->router->getRoutes())->map(function ($route) {
            return $this->getRouteInformation($route);
        })->filter()->all();
    }

    protected function getCookie($cookie_name = 'session_cookie', $ignore_session_cookie = false, $ignore_csrf_cookie = false)
    {
        $comment = '';
        if (!$ignore_session_cookie) {
            $comment .= <<<Comment
 *      @OA\\Parameter(
 *          name="{$cookie_name}",
 *          description="session cookie",
 *          @OA\\Schema(
 *              format="string"
 *          ),
 *          in="cookie",
 *          required=false
 *      ),

Comment;
        }
        if (!$ignore_csrf_cookie) {
            $comment .= <<<'Comment'
 *      @OA\Parameter(
 *          name="X-CSRF-TOKEN",
 *          description="CSRF-TOKEN",
 *          @OA\Schema(
 *              format="string"
 *          ),
 *          in="header",
 *          required=false
 *      ),
 *      @OA\Parameter(
 *          name="X-XSRF-TOKEN",
 *          description="CSRF-TOKEN",
 *          @OA\Schema(
 *              format="string"
 *          ),
 *          in="header",
 *          required=false
 *      ),

Comment;
        }

        return $comment;
    }

    /**
     * Get the route information for a given route.
     *
     * @param \Illuminate\Routing\Route $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        $action = '\\' . ltrim($route->getActionName(), '\\');

        return [
            'domain' => $route->domain(),
            'methods' => $route->methods(),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $action,
            'middleware' => $this->getMiddleware($route),
            'attributes' => $this->getAttributesByAction($action),
            'doc_comment' => $this->getDocCommentByAction($action),
        ];
    }

    protected function getAttributesByAction(string $action)
    {
        if (!strpos($action, '@')) {
            return [];
        }
        [$class, $method] = explode('@', $action);

        if (!class_exists($class)) {
            $this->error($class . 'is not found');
            return;
        }


        $refClas = new \ReflectionClass(\App::make($class));

        $attribute = $refClas->hasMethod($method) ? $refClas->getMethod($method)->getParameters() : [];
        foreach ($attribute as $key => $property) {
            $attribute[$key] = ['name' => $property->getName(),
                'type' => optional($property->getType())->getName(),
                'is_builtin' => optional($property->getType())->isBuiltin(),
            ];
        }

        return $attribute;
    }

    protected function getDocCommentByAction(string $action)
    {
        if (!strpos($action, '@')) {
            return '';
        }
        [$class, $method] = explode('@', $action);

        if (!class_exists($class)) {
            $this->error($class . 'is not found');
            return;
        }

        $refClas = new \ReflectionClass(\App::make($class));

        return $refClas->hasMethod($method) ? $refClas->getMethod($method)->getDocComment() : '';
    }

    /**
     * Get the middleware for the route.
     *
     * @param \Illuminate\Routing\Route $route
     * @return string
     */
    protected function getMiddleware($route)
    {
        return collect($this->router->gatherRouteMiddleware($route))->map(function ($middleware) {
            return $middleware instanceof \Closure ? 'Closure' : $middleware;
        })->implode("\n");
    }
}
