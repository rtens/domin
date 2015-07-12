<?php
namespace spec\rtens\domin\web\renderers;

use rtens\domin\files\MemoryFile;
use rtens\domin\web\renderers\FileRenderer;
use rtens\scrut\tests\statics\StaticTestSuite;

class FileRendererSpec extends StaticTestSuite {

    /** @var FileRenderer */
    private $renderer;

    protected function before() {
        $this->renderer = new FileRenderer();
    }

    function handleFiles() {
        $this->assert($this->renderer->handles(new MemoryFile('foo', 'foo/type')));
        $this->assert->not($this->renderer->handles(new \StdClass()));
        $this->assert->not($this->renderer->handles(null));
    }

    function renderLinkToDataUrl() {
        $this->assert($this->renderer->render(new MemoryFile('foo', 'foo/type', 'foo')),
            '<a href="data:foo/type;base64,Zm9v" target="_blank">foo</a>');
    }

    function renderImageWithDataUrl() {
        $this->assert($this->renderer->render(new MemoryFile('foo', 'image/type', 'foo')),
            '<img src="data:image/type;base64,Zm9v" style="max-height:10em"/>');
    }
}