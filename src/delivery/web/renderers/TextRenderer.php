<?php namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\web\Element;
use rtens\domin\parameters\Text;

class TextRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Text;
    }

    /**
     * @param Text $value
     * @return string
     */
    public function render($value) {
        return (string)new Element('div', ['style' => 'border: 1px solid silver; padding: 5pt;'], [
            $value->getContent()
        ]);
    }
}