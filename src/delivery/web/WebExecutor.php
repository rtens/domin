<?php
namespace rtens\domin\delivery\web;

use rtens\domin\Executor;

class WebExecutor extends Executor {

    private $headElements = [];

    /**
     * @return array|Element[]
     */
    public function getHeadElements() {
        return $this->headElements;
    }

    protected function render($value) {
        $renderer = $this->renderers->getRenderer($value);
        if ($renderer instanceof WebRenderer) {
            $this->headElements = $renderer->headElements($value);
        }
        return $renderer->render($value);
    }
}