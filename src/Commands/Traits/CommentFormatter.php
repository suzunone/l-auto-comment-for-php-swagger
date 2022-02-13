<?php

/**
 * This file is part of l-auto-comment-for-php-swagger
 *
 */

namespace AutoCommentForPHPSwagger\Commands\Traits;

trait CommentFormatter
{
    /**
     * pretty format annotation comment
     * @param string $format_comment
     * @param int $width
     * @return string
     */
    public function commentFormatter(string $format_comment, int $width = 2): string
    {
        $indent = 0;
        $comments = explode("\n", $format_comment);
        foreach ($comments as $key => $comment) {
            $comment = trim($comment);
            $before_indent = $indent;

            $first_string = substr($comment, 0, 1);
            $last_string = substr($comment, -1, 1);
            if ($last_string === '(') {
                $indent++;
            }
            if ($first_string === ')') {
                $indent--;
            }
            if ($last_string === '{') {
                $indent++;
            }
            if ($first_string === '}') {
                $indent--;
            }

            $indent = max(0, $indent);
            $space = '';
            if ($before_indent > $indent) {
                if ($indent > 0) {
                    $space =  str_repeat(' ', $indent * $width);
                }
            } elseif ($before_indent > 0) {
                $space = str_repeat(' ', $before_indent * $width);
            }

            $comments[$key] = rtrim(' * ' . $space . $comment);
        }

        return implode("\n", $comments);
    }
}
