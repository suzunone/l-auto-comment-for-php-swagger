<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Entities\Properties;

use AutoCommentForPHPSwagger\Libs\EmptyExample;

/**
 *
 */
class TypePrimitive extends PropertyBase
{
    public function comment(): string
    {
        $nullable = $this->nullable ? 'nullable=true' : 'nullable=false';

        if ($this->example instanceof EmptyExample || empty($this->example)) {
            return <<<COMMENT
@OA\\Property(property="{$this->name}", type="{$this->type}", {$nullable},description="{$this->description}"),

COMMENT;
        }

        return <<<COMMENT
@OA\\Property(property="{$this->name}", type="{$this->type}", {$nullable},description="{$this->description}",example={$this->example}),

COMMENT;
    }
}
