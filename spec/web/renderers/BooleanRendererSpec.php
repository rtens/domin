<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\web\renderers\BooleanRenderer;
use rtens\scrut\tests\statics\StaticTestSuite;

class BooleanRendererSpec extends StaticTestSuite {

    /** @var BooleanRenderer */
    private $renderer;

    protected function before() {
        $this->renderer = new BooleanRenderer();
    }

    function handlesBooleans() {
        $this->assert($this->renderer->handles(true));
        $this->assert($this->renderer->handles(false));
        $this->assert->not($this->renderer->handles(1));
        $this->assert->not($this->renderer->handles(0));
        $this->assert->not($this->renderer->handles('foo'));
    }

    function rendersBooleans() {
        $this->assert($this->renderer->render(true), 'Yes');
        $this->assert($this->renderer->render(false), 'No');
    }
}