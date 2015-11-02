<?php namespace rtens\domin\delivery\cli\renderers;

use rtens\domin\delivery\Renderer;

class DateIntervalRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof \DateInterval;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function render($value) {
        return ($value->d ? $value->d . 'd ' : '') . ($value ? $value->format('%H:%I') : '');
    }
}