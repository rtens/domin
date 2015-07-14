<?php
namespace spec\rtens\domin\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\web\renderers\object\ClassLink;
use rtens\domin\web\renderers\object\Link;
use rtens\domin\web\renderers\object\LinkRegistry;
use rtens\domin\web\renderers\object\ObjectRenderer;
use rtens\domin\web\renderers\PrimitiveRenderer;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\protocol\Url;

class ObjectRendererSpec extends StaticTestSuite {

    /** @var LinkRegistry */
    private $links;

    /** @var ObjectRenderer */
    private $renderer;

    /** @var RendererRegistry */
    private $renderers;

    protected function before() {
        $this->renderers = new RendererRegistry();
        $this->links = new LinkRegistry();
        $this->renderer = new ObjectRenderer($this->renderers, $this->links, Url::fromString('baser/url'));
    }

    function handlesObjects() {
        $this->assert($this->renderer->handles(new \DateTime()));
        $this->assert->not($this->renderer->handles('foo'));
        $this->assert->not($this->renderer->handles([]));
    }

    function emptyObject() {
        $this->assert($this->renderer->render(new \StdClass()),
            '<div class="panel panel-info">' . "\n" .
            '<div class="panel-heading clearfix">' . "\n" .
            '<h3 class="panel-title">' . "\n" .
            'stdClass' . "\n" .
            '<small class="pull-right"></small>' . "\n" .
            '</h3>' . "\n" .
            '</div>' . "\n" .
            '<div class="panel-body"><dl class="dl-horizontal"></dl></div>' . "\n" .
            '</div>'
        );
    }

    function renderProperties() {
        $propertyRenderer = Mockster::of(Renderer::class);
        $this->renderers->add(Mockster::mock($propertyRenderer));

        Mockster::stub($propertyRenderer->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($propertyRenderer->render(Argument::any()))->will()->forwardTo(function ($in) {
            return $in . ' rendered';
        });

        $object = new \StdClass();
        $object->foo = 'fos';
        $object->bar = 'bas';

        $this->assert->contains($this->renderer->render($object),
            '<dl class="dl-horizontal">' . "\n" .
            '<dt>Foo</dt>' . "\n" .
            '<dd>fos rendered</dd>' . "\n" .
            '<dt>Bar</dt>' . "\n" .
            '<dd>bas rendered</dd>' . "\n" .
            '</dl>'
        );
    }

    function onlyRenderReadableProperties() {
        $this->renderers->add(new PrimitiveRenderer());

        eval('class NotOnlyReadableProperties {
            public $public;
            function __construct($one) {}
            function getGetter() {}
            function setSetter($foo) {}
        }');
        $class = new \ReflectionClass('NotOnlyReadableProperties');

        $this->assert->contains($this->renderer->render($class->newInstance("uno")),
            '<dl class="dl-horizontal">' . "\n" .
            '<dt>Public</dt>' . "\n" .
            '<dd></dd>' . "\n" .
            '<dt>Getter</dt>' . "\n" .
            '<dd></dd>' . "\n" .
            '</dl>'
        );
    }

    function renderLinkedAction() {
        $this->renderers->add(new PrimitiveRenderer());

        $link = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($link));

        Mockster::stub($link->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($link->caption(Argument::any()))->will()->return_('Some Action');
        Mockster::stub($link->actionId())->will()->return_('foo');

        $object = new \StdClass();
        $object->foo = 'bar';

        $this->assert->contains($this->renderer->render($object),
            '<small class="pull-right">' .
            '<a class="btn btn-xs btn-primary" href="baser/url/foo">Some Action</a>' .
            '</small>');
    }

    function renderLinkedActionWithParameters() {
        $this->renderers->add(new PrimitiveRenderer());

        $link = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($link));

        Mockster::stub($link->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($link->caption(Argument::any()))->will()->return_('Some Action');
        Mockster::stub($link->actionId())->will()->return_('foo');
        Mockster::stub($link->parameters(Argument::any()))->will()->forwardTo(function ($object) {
            return [
                'bas' => $object->foo
            ];
        });

        $object = new \StdClass();
        $object->foo = 'bar';

        $this->assert->contains($this->renderer->render($object),
            '<small class="pull-right">' .
            '<a class="btn btn-xs btn-primary" href="baser/url/foo?bas=bar">Some Action</a>' .
            '</small>');
    }

    function filterLinkedActions() {
        $this->renderers->add(new PrimitiveRenderer());

        $linkOne = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($linkOne));
        Mockster::stub($linkOne->handles(Argument::any()))->will()->forwardTo(function ($object) {
            return isset($object->foo);
        });
        Mockster::stub($linkOne->caption(Argument::any()))->will()->forwardTo(function ($object) {
            return 'One ' . $object->foo;
        });

        $linkTwo = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($linkTwo));
        Mockster::stub($linkTwo->handles(Argument::any()))->will()->forwardTo(function ($object) {
            return isset($object->foo);
        });
        Mockster::stub($linkTwo->caption(Argument::any()))->will()->forwardTo(function ($object) {
            return 'Two ' . $object->foo;
        });

        $linkThree = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($linkThree));
        Mockster::stub($linkThree->handles(Argument::any()))->will()->forwardTo(function ($object) {
            return isset($object->bar);
        });

        $object = new \StdClass();
        $object->foo = 'bar';

        $this->assert->contains($this->renderer->render($object),
            '<small class="pull-right">' . "\n" .
            '<a class="btn btn-xs btn-primary" href="baser/url/">One bar</a>' . "\n" .
            '<a class="btn btn-xs btn-primary" href="baser/url/">Two bar</a>' . "\n" .
            '</small>');
    }

    function useClassLink() {
        $link = new ClassLink('fooBar', \DateTimeInterface::class, function (\DateTimeInterface $object) {
            return [
                'one' => $object->format('Y-m-d')
            ];
        });

        $this->assert($link->handles(new \DateTime()));
        $this->assert($link->handles(new \DateTimeImmutable()));
        $this->assert->not($link->handles(new \StdClass()));

        $this->assert($link->actionId(), 'fooBar');
        $this->assert($link->caption(new \DateTime()), 'Foo Bar');
        $this->assert($link->parameters(new \DateTime('13 December 2011')), ['one' => '2011-12-13']);
    }
} 