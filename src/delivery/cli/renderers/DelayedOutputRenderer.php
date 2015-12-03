<?php namespace rtens\domin\delivery\cli\renderers;

use rtens\domin\delivery\DelayedOutput;
use rtens\domin\delivery\Renderer;

class DelayedOutputRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof DelayedOutput;
    }

    /**
     * @param DelayedOutput $value
     * @return DelayedOutput
     */
    public function render($value) {
        return $value;
    }
}