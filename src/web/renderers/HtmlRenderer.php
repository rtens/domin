<?php
namespace rtens\domin\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\parameters\Html;

class HtmlRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Html;
    }

    /**
     * @param Html $value
     * @return string
     */
    public function render($value) {
        return $value->getContent();
    }
}