<?php
namespace spec\rtens\domin\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\web\renderers\ObjectRenderer;
use rtens\domin\web\renderers\PrimitiveRenderer;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

class ObjectRendererSpec extends StaticTestSuite {

    function handlesObjects() {
        $renderer = new ObjectRenderer(new RendererRegistry());

        $this->assert($renderer->handles(new \DateTime()));
        $this->assert->not($renderer->handles('foo'));
        $this->assert->not($renderer->handles([]));
    }

    function emptyObject() {
        $renderer = new ObjectRenderer(new RendererRegistry());

        $this->assert($renderer->render(new \StdClass()),
            '<div class="panel panel-info">' . "\n" .
            '<div class="panel-heading"><h3 class="panel-title">stdClass</h3></div>' . "\n" .
            '<div class="panel-body"><dl class="dl-horizontal"></dl></div>' . "\n" .
            '</div>'
        );
    }

    function renderProperties() {
        $renderers = new RendererRegistry();
        $renderer = new ObjectRenderer($renderers);

        $propertyRenderer = Mockster::of(Renderer::class);
        $renderers->add(Mockster::mock($propertyRenderer));

        Mockster::stub($propertyRenderer->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($propertyRenderer->render(Argument::any()))->will()->forwardTo(function ($in) {
            return $in . ' rendered';
        });

        $object = new \StdClass();
        $object->foo = 'fos';
        $object->bar = 'bas';

        $this->assert($renderer->render($object),
            '<div class="panel panel-info">' . "\n" .
            '<div class="panel-heading"><h3 class="panel-title">stdClass</h3></div>' . "\n" .
            '<div class="panel-body">' . "\n" .
            '<dl class="dl-horizontal">' . "\n" .
            '<dt>Foo</dt>' . "\n" .
            '<dd>fos rendered</dd>' . "\n" .
            '<dt>Bar</dt>' . "\n" .
            '<dd>bas rendered</dd>' . "\n" .
            '</dl>' . "\n" .
            '</div>' . "\n" .
            '</div>'
        );
    }

    function onlyRenderReadableProperties() {
        $renderers = new RendererRegistry();
        $renderers->add(new PrimitiveRenderer());

        eval('class NotOnlyReadableProperties {
            public $public;
            function __construct($one) {}
            function getGetter() {}
            function setSetter($foo) {}
        }');
        $class = new \ReflectionClass('NotOnlyReadableProperties');

        $renderer = new ObjectRenderer($renderers);
        $this->assert($renderer->render($class->newInstance("uno")),
            '<div class="panel panel-info">' . "\n" .
            '<div class="panel-heading"><h3 class="panel-title">NotOnlyReadableProperties</h3></div>' . "\n" .
            '<div class="panel-body">' . "\n" .
            '<dl class="dl-horizontal">' . "\n" .
            '<dt>Public</dt>' . "\n" .
            '<dd></dd>' . "\n" .
            '<dt>Getter</dt>' . "\n" .
            '<dd></dd>' . "\n" .
            '</dl>' . "\n" .
            '</div>' . "\n" .
            '</div>');
    }
} 