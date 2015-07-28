<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\web\renderers\IdentifierRenderer;
use rtens\domin\delivery\web\renderers\link\IdentifierLink;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\link\LinkRegistry;
use rtens\domin\delivery\web\WebCommentParser;
use rtens\domin\parameters\Identifier;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\protocol\Url;

class IdentifierRendererSpec extends StaticTestSuite {

    /** @var ActionRegistry */
    private $actions;

    /** @var LinkRegistry */
    private $links;

    /** @var IdentifierRenderer */
    private $renderer;

    protected function before() {
        $this->links = new LinkRegistry();
        $this->actions = new ActionRegistry();
        $printer = new LinkPrinter(Url::fromString('http://example.com/base'), $this->links, $this->actions, new WebCommentParser());
        $this->renderer = new IdentifierRenderer($printer);
    }

    function printId() {
        $this->assert($this->renderer->render(new Identifier(\StdClass::class, 'some-id')), 'some-id');
    }

    function printLinksAsDropDown() {
        $this->actions->add('foo', Mockster::mock(Action::class));
        $this->links->add(new IdentifierLink(\DateTime::class, 'foo', 'id'));

        $rendered = $this->renderer->render(new Identifier(\DateTime::class, 'foo-id'));
        $this->assert->contains($rendered, "DateTime\n<span class=\"caret\"></span>");
        $this->assert->contains($rendered,
            '<ul class="dropdown-menu">' .
            '<li><a class="" href="http://example.com/base/foo?id=foo-id"></a></li>' .
            '</ul>');
    }
}