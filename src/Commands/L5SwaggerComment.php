<?php

/**
 * This file is part of auto-comment-for-l5-swagger
 *
 */

namespace AutoCommentForL5Swagger\Commands;

use App\Http\Controllers\L5Swagger\OpenApiDoc;
use AutoCommentForL5Swagger\Commands\Traits\CommentFormatter;
use AutoCommentForL5Swagger\Libs\SwagIt;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;

class L5SwaggerComment extends Command
{
    use CommentFormatter;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openapi:l5-swagger-comment {type? : Config type to be used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate comments for PHPSwagger';

    protected $router;

    protected $config_root = 'auto-comment-for-l5-swagger.documentations.';

    protected $add_schema = "";

    /**
     * Execute the console command.
     *
     * @param \Illuminate\Routing\Router $Router
     * @return int
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function handle(Router $Router)
    {
        $type = $this->argument('type') ?? 'default';
        $this->config_root .= $type . '.';
        $OpenApiDoc = $this->laravel['config']->get($this->config_root . 'ControllerName', OpenApiDoc::class);

        // $this->call('make:controller', ['name' => 'L5Swagger/OpenApiDoc']);
        if (!class_exists($OpenApiDoc)) {
            $this->comment('please call php artisan make:controller ' . str_replace("\\", '/', $OpenApiDoc));

            return -1;
        }

        $OpenApiDocRef = new ReflectionClass(new $OpenApiDoc);
        $namespace = $OpenApiDocRef->getNamespaceName();

        $this->router = $Router;
        $route = $this->getRoutes();

        $comment = $this->templateToComment();

        $comment .= <<<'COMMENT'
/**

COMMENT;

        $open_api_comment = '';
        foreach ($route as $route_item) {
            foreach ($route_item['methods'] as $method) {
                $open_api_comment .= $this->getL5Comment($method, $route_item);
            }
        }

        $comment .= $this->commentFormatter($open_api_comment . $this->add_schema);

        $comment .= ' */';

        $stub = file_get_contents(__DIR__ . '/stubs/lg_swagger_controller.stub');

        $stub = str_replace(['// OpenApiDoc //', '__Namespaces__'], [$comment, $namespace], $stub);

        file_put_contents((new ReflectionClass($OpenApiDoc))->getFileName(), $stub);
    }

    /**
     * @param string $method
     * @param array $route_item
     * @return string
     */
    public function getL5Comment(string $method, array $route_item): string
    {
        $method = Str::studly(strtolower($method));

        $annotation = $this->parseAnnotation($route_item['doc_comment']);
        if (!count($annotation)) {
            return '';
        }

        $action = explode('@', $route_item['action'])[0];
        $action = str_replace(["\\App\\Http\\Controllers", 'Controller'], '', $action);
        $action = trim($action, "\\");

        $tags = explode("\\", $action);

        if (isset($annotation['openapi-tags'])) {
            $tags = [];
            foreach ($annotation['openapi-tags'] as $item) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $tags = array_merge($tags, $item);
            }
        }

        $tags = implode('","', $tags);

        $description = $this->parseDescription($route_item['doc_comment']);

        $operationId = strtolower($method) . $route_item['id'];

        $comment = <<<COMMENT
@OA\\{$method}(
tags={"{$tags}"},
operationId="{$operationId}",
path="/{$route_item['uri']}",
description="{$description}",


COMMENT;

        if (!isset($annotation['openapi-ignore-cookie'])) {
            $cookie_name = $annotation['openapi-cookie'][0][0] ?? $this->laravel['config']->get($this->config_root . 'session_cookie_name', 'session_cookie');
            $comment .= $this->getCookie($cookie_name, isset($annotation['openapi-ignore-session-cookie']), isset($annotation['openapi-ignore-csrf-cookie']));
        }

        foreach ($route_item['attributes'] as $attribute) {
            if (strpos($attribute['type'], 'App\\Http\\Requests') !== 0) {
                continue;
            }
            $comment .= $this->formRequestToComment($attribute['type']);
        }

        $comment .= $this->makePathOption($route_item['uri']);
        $comment .= $this->makeResponseTag($annotation, $route_item['id']);
        $comment .= <<<'COMMENT'
)


COMMENT;

        return $comment;
    }

    /**
     * @param string $url
     * @return string
     */
    public function makePathOption(string $url): string
    {
        preg_match_all('/\{([^}]*)\}/u', $url, $match);
        $comment = '';
        foreach ($match[1] as $path) {
            $comment .= <<<COMMENT
@OA\\Parameter(
name="{$path}",
description="{$path}_id",
@OA\\Schema(
format="int64",
type="integer"
),
in="path",
required=true
),

COMMENT;
        }

        return $comment;
    }

    /**
     * @param array $annotation
     * @param string|null $id
     * @return string
     */
    public function makeResponseTag(array $annotation, ?string $id = null):string
    {
        $comment = '';
        foreach ($annotation['openapi-response'] as $os_res) {
            $response = array_shift($os_res);
            $ref = array_shift($os_res);
            $description = implode(' ', $os_res);
            // simple response
            if (stripos($ref, '&') === false) {
                $comment .= <<<COMMENT
@OA\\Response(
response="{$response}",
description="{$description}",
@OA\\JsonContent(ref="{$ref}")
),

COMMENT;

                continue;
            }
            parse_str($ref, $ref_arr);

            $schema = $id . $response;

            $comment .= <<<COMMENT
@OA\\Response(
response="{$response}",
description="{$description}",
@OA\\JsonContent(ref="#/components/schemas/{$schema}")
),

COMMENT;

            // create add schema
            $this->add_schema .= $this->createSchema($ref_arr, $schema);
        }

        return $comment;
    }

    /**
     * @param array $ref_arr
     * @param string|null $id
     * @return string
     */
    public function createSchema(array $ref_arr, ?string $id):string
    {
        $sub_comment = '';
        $comment = <<<COMMENT
@OA\\Schema(
schema="{$id}",
type="object",
allOf={

COMMENT;

        foreach ($ref_arr as $key => $value) {
            if ($value === '') {
                $comment .= <<<COMMENT
@OA\\Schema(ref="{$key}"),

COMMENT;

                continue;
            }
            if (is_string($value)) {
                $comment .= <<<COMMENT
@OA\\Schema(
required={"{$key}"},
@OA\\Property(property="{$key}", ref="{$value}")
),

COMMENT;

                continue;
            }

            $sub_schema = $id . $key;
            $comment .= <<<COMMENT
@OA\\Schema(
required={"{$key}"},
@OA\\Property(property="{$key}", ref="#/components/schemas/{$sub_schema}")
),

COMMENT;

            $sub_comment .= $this->createSchema($value, $sub_schema);
        }

        $comment .= <<<'COMMENT'
}
),

COMMENT;

        return $comment . $sub_comment;
    }

    /**
     * @param string $doc_comment
     * @return string
     */
    public function parseDescription(string $doc_comment): string
    {
        preg_match_all('/^\/\*\*([^@]+)/u', $doc_comment, $match);

        $match = $match[1][0] ?? '';

        $match = mb_ereg_replace('\n +\* *', '', $match);
        $match = mb_ereg_replace('\n', ' ', $match);

        return trim($match);
    }

    /**
     * @param string $doc_comment
     * @return array
     */
    public function parseAnnotation(string $doc_comment): array
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

    /**
     * @param string $class_name
     * @return string
     */
    public function formRequestToComment(string $class_name): string
    {
        if (!class_exists($class_name)) {
            $this->error($class_name . 'is not found');

            return '';
        }

        $reflection = new ReflectionClass($class_name);

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
@OA\\Parameter(
name="{$match[3][$key]}",
description="{$match[4][$key]}",
@OA\\Schema(
{$format}
),
in="query",
required={$required}
),

COMMENT;
        }

        return $comment;
    }


    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes(): array
    {
        $ignored_route_names = $this->laravel['config']->get($this->config_root . 'ignored_route_names', []);
        $enabled_route_names = $this->laravel['config']->get($this->config_root . 'enabled_route_names', []);

        return collect($this->router->getRoutes())->map(function ($route) {
            return $this->getRouteInformation($route);
        })->filter(function ($item) use ($ignored_route_names, $enabled_route_names) {
            if (empty($enabled_route_names)) {
                return !in_array($item['name'], $ignored_route_names);
            }

            return in_array($item['name'], $enabled_route_names);
        })->all();
    }

    protected function getCookie($cookie_name = 'session_cookie', $ignore_session_cookie = false, $ignore_csrf_cookie = false): string
    {
        $comment = '';
        if (!$ignore_session_cookie) {
            $comment .= <<<Comment
@OA\\Parameter(
name="{$cookie_name}",
description="session cookie",
@OA\\Schema(
format="string"
),
in="cookie",
required=false
),

Comment;
        }
        if (!$ignore_csrf_cookie) {
            $comment .= <<<'Comment'
@OA\Parameter(
name="X-CSRF-TOKEN",
description="CSRF-TOKEN",
@OA\Schema(
format="string"
),
in="header",
required=false
),
@OA\Parameter(
name="X-XSRF-TOKEN",
description="CSRF-TOKEN",
@OA\Schema(
format="string"
),
in="header",
required=false
),

Comment;
        }

        return $comment;
    }

    /**
     * Get the route information for a given route.
     *
     * @param \Illuminate\Routing\Route $route
     * @return array
     * @throws \ReflectionException
     */
    protected function getRouteInformation(Route $route): array
    {
        $action = '\\' . ltrim($route->getActionName(), '\\');

        $uri = collect(explode('/', trim($route->uri(), '/')));
        $uri = $uri->map(function ($item) {
            $item = trim($item, '{}');
            $item = str_replace('.', '-', $item);

            return Str::studly(Str::snake($item));
        });
        $id = $uri->implode('');

        return [
            'domain' => $route->domain(),
            'methods' => $route->methods(),
            'uri' => $route->uri(),
            'id' => $id,
            'name' => $route->getName(),
            'action' => $action,
            'middleware' => $this->getMiddleware($route),
            'attributes' => $this->getAttributesByAction($action),
            'doc_comment' => $this->getDocCommentByAction($action),
        ];
    }

    /**
     * @param string $action
     * @return array|\ReflectionParameter[]
     * @throws \ReflectionException
     */
    protected function getAttributesByAction(string $action): array
    {
        if (!strpos($action, '@')) {
            return [];
        }
        [$class, $method] = explode('@', $action);

        if (!class_exists($class)) {
            $this->error($class . 'is not found');

            return [];
        }

        $refClas = new ReflectionClass(\App::make($class));

        $attribute = $refClas->hasMethod($method) ? $refClas->getMethod($method)->getParameters() : [];
        foreach ($attribute as $key => $property) {
            $attribute[$key] = ['name' => $property->getName(),
                'type' => optional($property->getType())->getName(),
                'is_builtin' => optional($property->getType())->isBuiltin(),
            ];
        }

        return $attribute;
    }

    /**
     * @param string $action
     * @return string
     * @throws \ReflectionException
     */
    protected function getDocCommentByAction(string $action): string
    {
        if (!strpos($action, '@')) {
            return '';
        }
        [$class, $method] = explode('@', $action);

        if (!class_exists($class)) {
            $this->error($class . 'is not found');

            return '';
        }

        $refClas = new ReflectionClass(\App::make($class));

        return $refClas->hasMethod($method) ? $refClas->getMethod($method)->getDocComment() : '';
    }

    /**
     * Get the middleware for the route.
     *
     * @param \Illuminate\Routing\Route $route
     * @return string
     */
    protected function getMiddleware($route): string
    {
        return collect($this->router->gatherRouteMiddleware($route))->map(function ($middleware) {
            return $middleware instanceof Closure ? 'Closure' : $middleware;
        })->implode("\n");
    }

    /**
     * json and yaml to comment
     *
     * @throws \JsonException
     * @return string
     */
    protected function templateToComment(): string
    {
        $swagger_json_files = $this->laravel['config']->get($this->config_root . 'swagger_json_files', []);
        $swagger_yaml_files = $this->laravel['config']->get($this->config_root . 'swagger_yaml_files', []);
        $comment = '';
        foreach ($swagger_json_files as $file_name) {
            $data = json_decode(file_get_contents($file_name), true, 512, JSON_THROW_ON_ERROR);

            $swagit = new SwagIt($this->option('tab-size'), $this->option('tab-init'));
            $comment .= $swagit->convert($data);
        }

        foreach ($swagger_yaml_files as $file_name) {
            $data = Yaml::parse(file_get_contents($file_name));

            $swagit = new SwagIt(2, 2);
            $comment .= $swagit->convert($data);
        }

        return $comment;
    }
}
