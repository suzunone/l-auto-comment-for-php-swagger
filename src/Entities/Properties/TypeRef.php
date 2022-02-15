<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Entities\Properties;

/**
 *
 */
class TypeRef extends PropertyBase
{
    public function comment(): string
    {
        $nullable = $this->nullable ? 'nullable=true' : 'nullable=false';

        return <<<COMMENT
@OA\\Property(property="{$this->name}", ref="{$this->ref}", ${nullable}, description="{$this->description}"),

COMMENT;
    }
}
