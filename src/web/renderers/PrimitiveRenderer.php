<?php
namespace rtens\domin\web\renderers;

use rtens\domin\delivery\Renderer;

class PrimitiveRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return !is_object($value) && !is_array($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function render($value) {
        return htmlentities((string)$value);
    }
}