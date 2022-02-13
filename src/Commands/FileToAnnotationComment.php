<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Commands;

use AutoCommentForPHPSwagger\Libs\SwagIt;
use Illuminate\Console\Command;

class FileToAnnotationComment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openapi:file-to-annotation {file_path} {--tab-size=4} {--tab-init=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a comment definition from yaml or json';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file_name = $this->argument('file_path');

        try {
            $data = json_decode($file_name, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $data = \Symfony\Component\Yaml\Yaml::parse($file_name);
        }

        $swagit = new SwagIt($this->option('tab-size'), $this->option('tab-init'));

        echo $swagit->convert($data);

        return 0;
    }
}
