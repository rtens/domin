<?php
namespace rtens\domin\cli\renderers;

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
        return '(html)' . PHP_EOL . $value->getContent();
    }
}