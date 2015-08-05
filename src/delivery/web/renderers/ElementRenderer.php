<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\web\Element;

class ElementRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Element;
    }

    /**
     * @param Element $value
     * @return mixed
     */
    public function render($value) {
        return (string)$value;
    }
}