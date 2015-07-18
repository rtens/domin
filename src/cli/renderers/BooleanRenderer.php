<?php
namespace rtens\domin\cli\renderers;

use rtens\domin\delivery\Renderer;

class BooleanRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return is_bool($value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function render($value) {
        return $value ? 'Yes' : 'No';
    }
}