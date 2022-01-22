<?php

/**
 * This file is part of auto-comment-for-l5-swagger
 *
 */

namespace AutoCommentForL5Swagger\Commands;

use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Symfony\Component\Console\Output\OutputInterface;

class ModelToOpenApiSchema extends ModelsCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'openapi:create-model-to-schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create open api schema file for models';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->input->setOption('write', true);
        return parent::handle();
    }

    /**
     * Get the stub file for the generator.
     *
     * @param $name
     * @return string
     */
    protected function getStub($name): string
    {
        return __DIR__ . '/stubs/' . $name . '.stub';
    }

    /** @noinspection SlowArrayOperationsInLoopInspection */
    protected function generateDocs($loadModels, $ignore = '')
    {
        $path = $this->laravel['path'] . '/Schemas/';
        $output = '';

        $hasDoctrine = interface_exists('Doctrine\DBAL\Driver');

        if (empty($loadModels)) {
            $models = $this->loadModels();
        } else {
            $models = [];
            foreach ($loadModels as $model) {
                $models = array_merge($models, explode(',', $model));
            }
        }

        $ignore = array_merge(
            explode(',', $ignore),
            $this->laravel['config']->get('auto-comment-for-l5-swagger.ignored_models', [])
        );

        foreach ($models as $name) {
            if (in_array($name, $ignore)) {
                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->comment("Ignoring model '${name}'");
                }

                continue;
            }
            $this->properties = [];
            $this->methods = [];
            if (class_exists($name)) {
                try {
                    // handle abstract classes, interfaces, ...
                    $reflectionClass = new \ReflectionClass($name);

                    if (!$reflectionClass->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
                        continue;
                    }

                    $this->comment("Loading model '${name}'", OutputInterface::VERBOSITY_VERBOSE);

                    if (!$reflectionClass->IsInstantiable()) {
                        // ignore abstract class or interface
                        continue;
                    }

                    $model = $this->laravel->make($name);

                    if ($hasDoctrine) {
                        $this->getPropertiesFromTable($model);
                    }

                    if (method_exists($model, 'getCasts')) {
                        $this->castPropertiesType($model);
                    }

                    $this->getPropertiesFromMethods($model);
                    $this->getSoftDeleteMethods($model);
                    $this->getCollectionMethods($model);
                    $this->getFactoryMethods($model);

                    $this->runModelHooks($model);

                    $hidden = $reflectionClass->getProperty('hidden');
                    $hidden->setAccessible(true);
                    $hidden = $hidden->getValue(new $name);

                    $OARequired = [];
                    $properties = '';
                    foreach ($this->properties as $VariableName => $property) {
                        $TypeProperty = $property['type'];
                        if (strpos($TypeProperty, "\\") !== false) {
                            continue;
                        }

                        if ($property['read'] === false) {
                            continue;
                        }

                        if (array_search($VariableName, $hidden) !== false) {
                            continue;
                        }

                        $TypeProperty = str_replace(['int', 'bool', 'boolbool', '|'], ['integer', 'boolean', 'bool', ','], $TypeProperty);

                        $_TypeProperty = $TypeProperty;
                        if (strpos($_TypeProperty, 'integer') !== false) {
                            $TypeProperty = '"integer"';
                        } elseif (strpos($_TypeProperty, 'string') !== false) {
                            $TypeProperty = '"string"';
                        } elseif (strpos($_TypeProperty, 'boolean') !== false) {
                            $TypeProperty = '"boolean"';
                        } else {
                            $TypeProperty = '"' . $TypeProperty . '"';
                        }

                        if (strpos($_TypeProperty, 'null')) {
                            $TypeProperty .= ',nullable=true';
                        }

                        $OARequired[] = '"' . $VariableName . '"';
                        $properties .= str_replace(['TypeProperty', 'VariableName', 'TypeDescription'], [$TypeProperty, $VariableName, $property['comment']], file_get_contents($this->getStub('oa_property')));
                    }

                    $schema = file_get_contents($this->getStub('oa_schema'));

                    $schema = str_replace(['// properties //', 'ClassName', 'OARequired'], [$properties, $reflectionClass->getShortName(), join(',', $OARequired)], $schema);

                    file_put_contents($path . $reflectionClass->getShortName() . '.php', $schema);

                    // $output .= $this->createPhpDocs($name);
                    $ignore[] = $name;
                    $this->nullableColumns = [];
                } catch (\Throwable $e) {
                    $this->error('Exception: ' . $e->getMessage() .
                        "\nCould not analyze class ${name}.\n\nTrace:\n" .
                        $e->getTraceAsString());
                }
            }
        }

        if (!$hasDoctrine) {
            $this->error(
                'Warning: `"doctrine/dbal": "~2.3"` is required to load database information. ' .
                'Please require that in your composer.json and run `composer update`.'
            );
        }

        return $output;
    }
}
