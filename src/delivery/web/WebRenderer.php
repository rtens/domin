<?php
namespace rtens\domin\delivery\web;

use rtens\domin\delivery\Renderer;

interface WebRenderer extends Renderer {

    /**
     * @param mixed $value
     * @return array|Element[]
     */
    public function headElements($value);
}