<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\parameters\file\MemoryFile;
use rtens\domin\delivery\web\renderers\FileRenderer;
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
        $this->assert($this->renderer->render(new MemoryFile('foo.file', 'foo/type', 'foo')),
            '<a download="foo.file" href="data:foo/type;base64,Zm9v" target="_blank">foo.file</a>');
    }

    function renderImageWithDataUrl() {
        $this->assert($this->renderer->render(new MemoryFile('foo.img', 'image/type', 'foo')),
            '<img title="foo.img" src="data:image/type;base64,Zm9v" style="max-height:10em"/>');
    }
}