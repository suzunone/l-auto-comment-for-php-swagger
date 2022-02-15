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
class TypeRefArray extends PropertyBase
{
    public function comment(): string
    {
        if ($this->ref) {
            $array_type = 'ref="' . $this->ref . '"';
        } elseif (!empty($this->example) && !$this->example instanceof EmptyExample) {
            $array_type = 'type="' . $this->type . '", example=' . $this->example . '';
        } else {
            $array_type = 'type="' . $this->type . '"';
        }
        $item = ',@OA\Items(' . $array_type . ')';

        $nullable = $this->nullable ? 'nullable=true' : 'nullable=false';

        return <<<COMMENT
@OA\\Property(property="{$this->name}", type="array", {$nullable}, description="{$this->description}"{$item}),

COMMENT;
    }
}
