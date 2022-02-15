<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Commands;

use App\Http\Controllers\Swagger\SecuritySchemeOpenApiDoc;
use AutoCommentForPHPSwagger\Commands\Traits\CommentFormatter;
use Illuminate\Console\Command;
use ReflectionClass;

class SecurityComment extends Command
{
    use CommentFormatter;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openapi:security-comment {type? : Config type to be used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Security Setting file';

    protected $config_root = 'auto-comment-for-php-swagger.documentations.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type') ?? 'default';
        $this->config_root .= $type . '.';
        $OpenApiDoc = $this->laravel['config']->get($this->config_root . 'security_scheme_class_name', SecuritySchemeOpenApiDoc::class);

        if (!class_exists($OpenApiDoc)) {
            $this->comment('please call php artisan make:controller ' . str_replace("\\", '/', $OpenApiDoc));

            return -1;
        }

        $OpenApiDocRef = new \ReflectionClass(new $OpenApiDoc);
        $namespace = $OpenApiDocRef->getNamespaceName();

        $comment = '';

        $comment .= <<<'COMMENT'
/**

COMMENT;

        $open_api_comment = $this->createSecurity();

        $comment .= $this->commentFormatter($open_api_comment);

        $comment .= ' */';

        $stub = file_get_contents(__DIR__ . '/stubs/lg_swagger_controller.stub');

        $stub = str_replace(['// OpenApiDoc //', '__Namespaces__', '__OpenApiDoc__'], [$comment, $namespace, $OpenApiDocRef->getShortName()], $stub);

        $file_name = (new ReflectionClass($OpenApiDoc))->getFileName();
        $this->info('write:' . $file_name);
        file_put_contents($file_name, $stub);

        return 0;
    }

    public function createSecurity()
    {
        $open_api_info = $this->laravel['config']->get($this->config_root . 'security_schemes', []);
        $res = '';

        foreach ($open_api_info as $securityScheme => $value) {
            if (!$value['enable']) {
                continue;
            }

            unset($value['enable']);

            $res .= '@OA\SecurityScheme(' . "\n";
            $res .= $this->keyValue('securityScheme', $securityScheme);
            $res .= $this->createSecuritySub($value);
            $res .= ')' . "\n";
        }

        return $res;
    }

    public function createSecuritySub($arr)
    {
        $res = '';
        foreach ($arr as $key => $value) {
            if ($key === 'flows') {
                $res .= $this->createFlow($value);

                continue;
            }

            $res .= $this->keyValue($key, $value);
        }

        return $res;
    }

    public function createFlow($arr)
    {
        $res = '@OA\Flow(' . "\n";
        $res .= $this->createFlowSub($arr);
        $res .= ')' . "\n";

        return $res;
    }

    public function createFlowSub($arr)
    {
        $res = '';
        foreach ($arr as $key => $value) {
            $res .= $this->keyValue($key, $value);
        }

        return $res;
    }

    public function keyValue($key, $value)
    {
        if (is_array($value)) {
            $value = json_encode($value);

            return  <<<COMMENT
    {$key}={$value},

COMMENT;
        }

        return  <<<COMMENT
    {$key}="{$value}",

COMMENT;
    }
}
