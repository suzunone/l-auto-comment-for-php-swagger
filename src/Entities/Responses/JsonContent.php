<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Entities\Responses;

class JsonContent implements ResponseType
{
    public function comment(string $schema): string
    {
        return <<<COMMENT
    @OA\\JsonContent(ref="{$schema}"),

COMMENT;
    }
}
