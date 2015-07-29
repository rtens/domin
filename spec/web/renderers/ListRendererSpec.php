<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\renderers\ListRenderer;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

class ListRendererSpec extends StaticTestSuite {

    function emptyArray() {
        $renderer = new ListRenderer(new RendererRegistry());

        $this->assert($renderer->handles([]));
        $this->assert->not($renderer->handles(''));
        $this->assert->not($renderer->handles(new \StdClass()));

        $this->assert($renderer->render([]), '<ul class="list-unstyled"></ul>');
    }

    function nonEmptyArray() {
        $renderers = new RendererRegistry();

        $itemRenderer = Mockster::of(Renderer::class);
        $renderers->add(Mockster::mock($itemRenderer));

        Mockster::stub($itemRenderer->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($itemRenderer->render(Argument::any()))->will()->forwardTo(function ($item) {
            return $item . ' rendered';
        });

        $renderer = new ListRenderer($renderers);

        $this->assert($renderer->render(['one', 'two']),
            '<ul class="list-unstyled">' . "\n" .
            "<li>one rendered</li>" . "\n" .
            "<li>two rendered</li>" . "\n" .
            "</ul>"
        );
    }
} 