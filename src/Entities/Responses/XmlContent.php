<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Entities\Responses;

class XmlContent implements ResponseType
{
    public function comment(string $schema): string
    {
        return <<<COMMENT
    @OA\\XmlContent(ref="{$schema}"),

COMMENT;
    }
}
