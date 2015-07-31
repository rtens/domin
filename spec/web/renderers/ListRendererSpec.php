<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\ListRenderer;
use rtens\domin\delivery\web\WebRenderer;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

class ListRendererSpec extends StaticTestSuite {

    /** @var ListRenderer */
    private $renderer;

    /** @var RendererRegistry */
    private $registry;

    protected function before() {
        $this->registry = new RendererRegistry();
        $this->renderer = new ListRenderer($this->registry);
    }

    function handlesArraysWithNumericKeys() {
        $this->assert($this->renderer->handles([]));
        $this->assert($this->renderer->handles(['ome', 'two']));
        $this->assert($this->renderer->handles([3 => 'one', 10 => 'two']));
        $this->assert->not($this->renderer->handles(['uno' => 'one', 'two' => 'dos']));
    }

    function emptyArray() {
        $this->assert($this->renderer->handles([]));
        $this->assert->not($this->renderer->handles(''));
        $this->assert->not($this->renderer->handles(new \StdClass()));

        $this->assert($this->renderer->render([]), '<ul class="list-unstyled"></ul>');
    }

    function nonEmptyArray() {
        $itemRenderer = Mockster::of(Renderer::class);
        $this->registry->add(Mockster::mock($itemRenderer));

        Mockster::stub($itemRenderer->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($itemRenderer->render(Argument::any()))->will()->forwardTo(function ($item) {
            return $item . ' rendered';
        });

        $this->renderer = new ListRenderer($this->registry);

        $this->assert($this->renderer->render(['one', 'two']),
            '<ul class="list-unstyled">' . "\n" .
            "<li>one rendered</li>" . "\n" .
            "<li>two rendered</li>" . "\n" .
            "</ul>"
        );
    }

    function collectsHeadElementsOfItems() {
        $itemRenderer = Mockster::of(WebRenderer::class);
        $this->registry->add(Mockster::mock($itemRenderer));
        Mockster::stub($itemRenderer->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($itemRenderer->headElements('foo'))->will()->return_([new Element('foo')]);
        Mockster::stub($itemRenderer->headElements(Argument::object(\DateTime::class)))->will()->return_([new Element('bar')]);

        $elements = $this->renderer->headElements(['foo', new \DateTime()]);
        $this->assert->size($elements, 2);
        $this->assert((string)$elements[0], '<foo></foo>');
        $this->assert((string)$elements[1], '<bar></bar>');
    }
} 