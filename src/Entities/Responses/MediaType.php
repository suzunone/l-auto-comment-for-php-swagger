<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Entities\Responses;

class MediaType implements ResponseType
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function comment(string $schema): string
    {
        return <<<COMMENT
    @OA\\MediaType(
        mediaType="{$this->type}",
        @OA\\Schema(ref="{$schema}")
    ),
    
COMMENT;
    }
}
