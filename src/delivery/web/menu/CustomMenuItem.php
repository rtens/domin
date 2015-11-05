<?php namespace rtens\domin\delivery\web\menu;

use rtens\domin\delivery\web\Element;
use watoki\curir\delivery\WebRequest;

class CustomMenuItem implements MenuItem {

    /** @var callable */
    private $content;

    /**
     * CustomMenuItem constructor.
     * @param \rtens\domin\delivery\web\Element|string|callable $content The callable will be called with WebRequest
     */
    public function __construct($content) {
        if (!is_callable($content)) {
            $content = function () use ($content) {
                return $content;
            };
        }
        $this->content = $content;
    }

    public function render(WebRequest $request) {
        return new Element('li', [], [
            call_user_func($this->content, $request)
        ]);
    }
}