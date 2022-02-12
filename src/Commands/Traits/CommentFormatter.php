<?php
/**
 * CommentFormatter.php
 *
 * Class CommentFormatter
 *
 * @category   codingTests
 * @package    AutoCommentForL5Swagger\Commands\Traits
 * @subpackage AutoCommentForL5Swagger\Commands\Traits
 * @author     suzunone<suzunone.eleven@gmail.com>
 * @copyright  Project codingTests
 * @license    BSD 3-Clause License
 * @version    1.0
 * @link       https://github.com/suzunone/codingTests
 * @see        https://github.com/suzunone/codingTests
 * @since      2022/02/13
 */

namespace AutoCommentForL5Swagger\Commands\Traits;

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
