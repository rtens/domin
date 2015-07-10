<?php
namespace spec\rtens\domin\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\web\renderers\ArrayRenderer;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

class ArrayRendererSpec extends StaticTestSuite {

    function emptyArray() {
        $renderer = new ArrayRenderer(new RendererRegistry());

        $this->assert($renderer->handles([]));
        $this->assert->not($renderer->handles(''));
        $this->assert->not($renderer->handles(new \StdClass()));

        $this->assert($renderer->render([]), '<ul/>');
    }

    function nonEmptyArray() {
        $renderers = new RendererRegistry();

        $itemRenderer = Mockster::of(Renderer::class);
        $renderers->add(Mockster::mock($itemRenderer));

        Mockster::stub($itemRenderer->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($itemRenderer->render(Argument::any()))->will()->forwardTo(function ($item) {
            return $item . ' rendered';
        });

        $renderer = new ArrayRenderer($renderers);

        $this->assert($renderer->render(['one', 'two']),
            "<ul>" . "\n" .
            "<li>one rendered</li>" . "\n" .
            "<li>two rendered</li>" . "\n" .
            "</ul>"
        );
    }
} 