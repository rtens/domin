<?php
namespace rtens\domin\delivery;

interface Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value);

    /**
     * @param mixed $value
     * @return mixed
     */
    public function render($value);
}