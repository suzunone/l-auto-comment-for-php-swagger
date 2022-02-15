<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Entities\Properties;

use AutoCommentForPHPSwagger\Libs\EmptyExample;

/**
 * Class PropertyBase
 *
 */
abstract class PropertyBase implements PropertyInterface
{
    protected $ref;
    protected $property;
    protected $type;
    protected $example;
    protected $description;
    protected $nullable = false;

    public function __construct(?string $name = null, ?string $description = null)
    {
        $this->name = $name;
    }

    public function setRef($setter)
    {
        $this->ref = $setter;
    }

    public function setType($setter)
    {
        $this->type = $setter;
    }

    public function setProperty($setter)
    {
        $this->property = $setter;
    }

    public function setExample($setter)
    {
        if (empty($setter)) {
            $this->example = new EmptyExample();

            return;
        }
        if ($setter instanceof EmptyExample) {
            $this->example = new EmptyExample();

            return;
        }

        $this->example = json_encode($setter, JSON_THROW_ON_ERROR);
    }

    public function setDescription($setter)
    {
        $this->description = $setter;
    }
    
    public function setNullable($setter)
    {
        $this->nullable = $setter;
    }
}
