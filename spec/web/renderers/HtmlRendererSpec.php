<?php
namespace spec\rtens\domin\web\renderers;

use rtens\domin\parameters\Html;
use rtens\domin\web\renderers\HtmlRenderer;
use rtens\scrut\tests\statics\StaticTestSuite;

class HtmlRendererSpec extends StaticTestSuite {

    /** @var HtmlRenderer */
    private $renderer;

    protected function before() {
        $this->renderer = new HtmlRenderer();
    }

    function handlesHtmlObjects() {
        $this->assert($this->renderer->handles(new Html('foo')));
        $this->assert->not($this->renderer->handles(new \StdClass()));
        $this->assert->not($this->renderer->handles('foo'));
    }

    function rendersContent() {
        $this->assert($this->renderer->render(new Html('some <html/>')),
            '<div style="border: 1px solid silver;">some <html/></div>');
    }
}