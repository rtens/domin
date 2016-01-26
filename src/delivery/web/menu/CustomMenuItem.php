<?php namespace rtens\domin\delivery\web\menu;

use rtens\domin\delivery\web\Element;

class CustomMenuItem implements MenuItem {

    /** @var callable */
    private $content;

    /**
     * CustomMenuItem constructor.
     * @param Element|string|callable $content
     */
    public function __construct($content) {
        if (!is_callable($content)) {
            $content = function () use ($content) {
                return $content;
            };
        }
        $this->content = $content;
    }

    public function render() {
        return new Element('li', [], [
            call_user_func($this->content)
        ]);
    }
}