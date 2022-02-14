<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Entities\Responses;

interface ResponseType
{
    public function comment(string $schema): string;
}
