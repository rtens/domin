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
     * @param mixed $value
     * @return mixed
     */
    public function render($value) {
        return (string)$value;
    }
}