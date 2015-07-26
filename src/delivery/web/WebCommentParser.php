<?php
namespace rtens\domin\delivery\web;

use rtens\domin\reflection\CommentParser;

class WebCommentParser extends CommentParser {

    public function parse($comment) {
        return nl2br(parent::parse($comment));
    }
}