<?php
namespace rtens\domin\delivery\web;

use rtens\domin\reflection\CommentParser;

class WebCommentParser extends CommentParser {

    public function shorten($description) {
        return strip_tags(explode('</p>', explode("\n\n", $description)[0])[0], '<b><i><s><u><strong><em>');
    }

    public function parse($comment) {
        if (class_exists(\Parsedown::class)) {
            return (new \Parsedown())->text($comment);
        }
        return nl2br(parent::parse($comment));
    }
}