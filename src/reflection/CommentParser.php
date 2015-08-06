<?php
namespace rtens\domin\reflection;

class CommentParser {

    public function parse($comment) {
        return $comment;
    }

    public function shorten($description) {
        return explode("\n\n", $description)[0];
    }
}