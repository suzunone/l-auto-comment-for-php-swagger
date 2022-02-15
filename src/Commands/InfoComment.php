<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Commands;

use App\Http\Controllers\Swagger\OpenApiInfo;
use AutoCommentForPHPSwagger\Commands\Traits\CommentFormatter;
use Illuminate\Console\Command;
use ReflectionClass;

class InfoComment extends Command
{
    use CommentFormatter;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openapi:info-comment {type? : Config type to be used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create OA\\Info file';

    protected $config_root = 'auto-comment-for-php-swagger.documentations.';

    /**
     * Execute the console command.
     *
     * @throws \ReflectionException
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type') ?? 'default';
        $this->config_root .= $type . '.';
        $OpenApiDoc = $this->laravel['config']->get($this->config_root . 'info_class_name', OpenApiInfo::class);

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

        $open_api_comment = $this->createInfo() . "\n" . $this->commentFormatter($this->createServers() . $this->createTags()) . "\n";

        $comment .= $open_api_comment;

        $comment .= ' */';

        $stub = file_get_contents(__DIR__ . '/stubs/lg_swagger_controller.stub');

        $stub = str_replace(['// OpenApiDoc //', '__Namespaces__', '__OpenApiDoc__'], [$comment, $namespace, $OpenApiDocRef->getShortName()], $stub);

        $file_name = (new ReflectionClass($OpenApiDoc))->getFileName();
        $this->info('write:' . $file_name);
        file_put_contents($file_name, $stub);

        return 0;
    }

    public function createInfo()
    {
        $open_api_info = $this->laravel['config']->get($this->config_root . 'open_api_info', []);

        $info['title'] = $open_api_info['title'] ?? 'Open API Reference';
        $info['description'] = $open_api_info['description'] ?? '';
        $info['version'] = $open_api_info['version'] ?? '1.0.0';
        $info['termsOfService'] = $open_api_info['termsOfService'] ?? null;

        $res = '@OA\Info(' . "\n";

        $description = '';
        foreach ($info as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($key === 'description') {
                $description .= $this->keyValue($key, $value);

                continue;
            }
            $res .= $this->keyValue($key, $value);
        }

        // contact
        if (!empty($open_api_info['contact']) && !empty($open_api_info['contact']['email']) && !empty($open_api_info['contact']['url'])&& !empty($open_api_info['contact']['name'])) {
            $res .= '@OA\Contact(' . "\n";

            foreach ($open_api_info['contact'] as $key => $value) {
                if ($value === null) {
                    continue;
                }

                $res .= $this->keyValue($key, $value);
            }

            $res .= ')' . "\n";
        }

        // licence
        if (!empty($open_api_info['licence'])) {
            $res = '@OA\License(' . "\n";

            foreach ($open_api_info['licence'] as $key => $value) {
                if ($value === null) {
                    continue;
                }
                $res .= $this->keyValue($key, $value);
            }

            $res .= ')' . "\n";
        }

        $res = $this->commentFormatter($res . "\n");

        $res .= $description;
        $res .= $this->commentFormatter(')' . "\n");

        return $res;
    }

    public function createTags()
    {
        $open_api_info = $this->laravel['config']->get($this->config_root . 'open_api_info.tags', []);

        $res = '';

        foreach ($open_api_info as $key => $item) {
            $res .= $this->createTag($key, $item);
        }

        return $res;
    }

    public function createServers()
    {
        $open_api_info = $this->laravel['config']->get($this->config_root . 'open_api_info.servers', []);

        $res = '';

        foreach ($open_api_info as $item) {
            $res .= $this->createServer($item);
        }

        return $res;
    }

    public function createTag($tagName, $value)
    {
        $res = '@OA\Tag(' . "\n";

        $info = [
            'name' => $tagName,
            'description' => $value['description'] ?? '',
        ];

        $ExternalDocumentation = $value['external_documentation'] ?? [];

        foreach ($info as $key => $item) {
            if ($item === null) {
                continue;
            }

            $res .= $this->keyValue($key, $item);
        }

        if (!empty($ExternalDocumentation)) {
            $res .= '@OA\ExternalDocumentation(' . "\n";

            foreach ($ExternalDocumentation as $key => $item) {
                if ($item === null) {
                    continue;
                }

                $res .= $this->keyValue($key, $item);
            }

            $res .= ')' . "\n";
        }

        $res .= ')' . "\n";

        return $res;
    }

    public function createServer($value)
    {
        $res = '@OA\Server(' . "\n";

        $info = [
            'description' => $value['description'] ?? '',
            'url' => $value['url'] ?? '',
        ];

        $ExternalDocumentation = $value['external_documentation'] ?? [];

        foreach ($info as $key => $item) {
            if ($item === null) {
                continue;
            }

            $res .= $this->keyValue($key, $item);
        }

        if (!empty($ExternalDocumentation)) {
            $res .= '@OA\ExternalDocumentation(' . "\n";

            foreach ($ExternalDocumentation as $key => $item) {
                if ($item === null) {
                    continue;
                }

                $res .= $this->keyValue($key, $item);
            }

            $res .= ')' . "\n";
        }

        $res .= ')' . "\n";

        return $res;
    }

    public function createExternalDocumentation()
    {
        $res = '@OA\ExternalDocumentation(' . "\n";

        $external_documentation = [
            'description' => $value['external_documentation']['description'] ?? '',
            'url' => $value['external_documentation']['url'] ?? '',
        ];

        foreach ($external_documentation as $key => $item) {
            if ($item === null) {
                continue;
            }

            $res .= $this->keyValue($key, $item);
        }

        $res .= ')' . "\n";

        return $res;
    }

    public function keyValue($key, $value)
    {
        return  <<<COMMENT
    {$key}="{$value}",

COMMENT;
    }
}
