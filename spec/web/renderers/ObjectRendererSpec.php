<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\reflection\types\TypeFactory;
use rtens\domin\delivery\web\renderers\link\ClassLink;
use rtens\domin\delivery\web\renderers\link\Link;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\link\LinkRegistry;
use rtens\domin\delivery\web\renderers\ObjectRenderer;
use rtens\domin\delivery\web\renderers\PrimitiveRenderer;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\protocol\Url;

class ObjectRendererSpec extends StaticTestSuite {

    /** @var ActionRegistry */
    private $actions;

    /** @var LinkRegistry */
    private $links;

    /** @var ObjectRenderer */
    private $renderer;

    /** @var RendererRegistry */
    private $renderers;

    protected function before() {
        $this->renderers = new RendererRegistry();
        $this->links = new LinkRegistry();
        $this->actions = new ActionRegistry();
        $this->renderer = new ObjectRenderer($this->renderers, new TypeFactory(),
            new LinkPrinter(Url::fromString('baser/url'), $this->links, $this->actions));
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
            public $public = "foo";
            function __construct($one) { $this->dynamic = $one; }
            function getGetter() { return "bar"; }
            function setSetter($foo) {}
        }');
        $class = new \ReflectionClass('NotOnlyReadableProperties');

        $this->assert->contains($this->renderer->render($class->newInstance("uno")),
            '<dl class="dl-horizontal">' . "\n" .
            '<dt>Public</dt>' . "\n" .
            '<dd>foo</dd>' . "\n" .
            '<dt>Dynamic</dt>' . "\n" .
            '<dd>uno</dd>' . "\n" .
            '<dt>Getter</dt>' . "\n" .
            '<dd>bar</dd>' . "\n" .
            '</dl>'
        );
    }

    function strikeNullProperties() {
        $this->renderers->add(new PrimitiveRenderer());

        eval('class APrettyEmptyProperty {
            public $public;
        }');
        $class = new \ReflectionClass('APrettyEmptyProperty');

        $this->assert->contains($this->renderer->render($class->newInstance()),
            '<dl class="dl-horizontal">' . "\n" .
            '<dt><s>Public</s></dt>' . "\n" .
            '<dd></dd>' . "\n" .
            '</dl>'
        );
    }

    /**
     * @throws \Exception
     */
    function renderLinkedAction() {
        $this->renderers->add(new PrimitiveRenderer());

        $link = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($link));

        Mockster::stub($link->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($link->actionId())->will()->return_('foo');

        $action = Mockster::of(Action::class);
        $this->actions->add('foo', Mockster::mock($action));
        Mockster::stub($action->caption())->will()->return_('Foo');
        Mockster::stub($action->description())->will()->return_("Foo description\n\nWith two lines.");

        $object = new \StdClass();
        $object->foo = 'bar';

        $this->assert->contains($this->renderer->render($object),
            '<small class="pull-right">' .
            '<a class="btn btn-xs btn-primary" href="baser/url/foo" title="Foo description">Foo</a>' .
            '</small>');
    }

    function renderLinkedActionWithParameters() {
        $this->renderers->add(new PrimitiveRenderer());

        $link = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($link));

        $this->actions->add('foo', Mockster::mock(Action::class));

        Mockster::stub($link->handles(Argument::any()))->will()->return_(true);
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
            '<a class="btn btn-xs btn-primary" href="baser/url/foo?bas=bar"></a>' .
            '</small>');
    }

    function filterLinkedActions() {
        $this->renderers->add(new PrimitiveRenderer());

        $linkOne = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($linkOne));
        Mockster::stub($linkOne->actionId())->will()->return_('one');
        Mockster::stub($linkOne->handles(Argument::any()))->will()->forwardTo(function ($object) {
            return isset($object->foo);
        });

        $linkTwo = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($linkTwo));
        Mockster::stub($linkTwo->actionId())->will()->return_('two');
        Mockster::stub($linkTwo->handles(Argument::any()))->will()->forwardTo(function ($object) {
            return isset($object->foo);
        });

        $linkThree = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($linkThree));
        Mockster::stub($linkThree->handles(Argument::any()))->will()->forwardTo(function ($object) {
            return isset($object->bar);
        });

        $this->actions->add('one', Mockster::mock(Action::class));
        $this->actions->add('two', Mockster::mock(Action::class));

        $object = new \StdClass();
        $object->foo = 'bar';

        $this->assert->contains($this->renderer->render($object),
            '<small class="pull-right">' . "\n" .
            '<a class="btn btn-xs btn-primary" href="baser/url/one"></a>' . "\n" .
            '<a class="btn btn-xs btn-primary" href="baser/url/two"></a>' . "\n" .
            '</small>');
    }

    function confirmActions() {
        $this->renderers->add(new PrimitiveRenderer());

        $link = Mockster::of(Link::class);
        $this->links->add(Mockster::mock($link));

        $this->actions->add('', Mockster::mock(Action::class));

        Mockster::stub($link->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($link->confirm())->will()->return_('Foo?');

        $object = new \StdClass();
        $object->foo = 'bar';

        $this->assert->contains($this->renderer->render($object),
            '<a class="btn btn-xs btn-primary" href="baser/url/" onclick="return confirm(\'Foo?\');"></a>');
    }

    function useClassLink() {
        $link = new ClassLink(\DateTimeInterface::class, 'fooBar', function (\DateTimeInterface $object) {
            return [
                'one' => $object->format('Y-m-d')
            ];
        });

        $this->assert($link->handles(new \DateTime()));
        $this->assert($link->handles(new \DateTimeImmutable()));
        $this->assert->not($link->handles(new \StdClass()));

        $this->assert($link->actionId(), 'fooBar');
        $this->assert($link->parameters(new \DateTime('13 December 2011')), ['one' => '2011-12-13']);

        $link = $link->setHandles(function (\DateTime $object) {
            return $object->format('Y') == '2011';
        });
        $this->assert($link->handles(new \DateTime('2011-12-13')));
        $this->assert->not($link->handles(new \DateTime('2012-12-13')));

        $this->assert->isNull($link->confirm());
        $link->setConfirmation('Foo?');
        $this->assert($link->confirm(), 'Foo?');
    }
} 