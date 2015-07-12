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

        $this->assert($renderer->render(new \StdClass()), '<h3>stdClass</h3>');
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
            "<h3>stdClass</h3>" . "\n" .
            "<div>" . "\n" .
            "<h4>foo</h4>" . "\n" .
            "<p>fos rendered</p>" . "\n" .
            "</div>" . "\n" .
            "<div>" . "\n" .
            "<h4>bar</h4>" . "\n" .
            "<p>bas rendered</p>" . "\n" .
            "</div>"
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
        $this->assert($renderer->render($class->newInstance("uno")), '<h3>NotOnlyReadableProperties</h3>' . "\n" .
            '<div>' . "\n" .
            '<h4>public</h4>' . "\n" .
            '<p></p>' . "\n" .
            '</div>' . "\n" .
            '<div>' . "\n" .
            '<h4>getter</h4>' . "\n" .
            '<p></p>' . "\n" .
            '</div>');
    }
} 