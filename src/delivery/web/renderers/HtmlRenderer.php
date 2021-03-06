<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\parameters\Html;
use rtens\domin\delivery\web\Element;

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
        return (string)new Element('div', ['style' => 'border: 1px solid black; padding: 5pt;'], [
            $value->getContent()
        ]);
    }
}