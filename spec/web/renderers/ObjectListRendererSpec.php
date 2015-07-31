<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\DateTimeRenderer;
use rtens\domin\delivery\web\renderers\link\ClassLink;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\link\LinkRegistry;
use rtens\domin\delivery\web\renderers\MapRenderer;
use rtens\domin\delivery\web\renderers\ObjectListRenderer;
use rtens\domin\delivery\web\renderers\ObjectRenderer;
use rtens\domin\delivery\web\renderers\PrimitiveRenderer;
use rtens\domin\delivery\web\renderers\table\DefaultTableConfiguration;
use rtens\domin\delivery\web\renderers\table\GenericTableConfiguration;
use rtens\domin\delivery\web\renderers\table\TableConfigurationRegistry;
use rtens\domin\delivery\web\WebCommentParser;
use rtens\domin\delivery\web\WebRenderer;
use rtens\domin\reflection\types\TypeFactory;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\protocol\Url;

/**
 * @property \rtens\scrut\fixtures\ExceptionFixture try <-
 */
class ObjectListRendererSpec extends StaticTestSuite {
    private $objectRenderer;

    /** @var RendererRegistry */
    private $renderers;

    /** @var LinkRegistry */
    private $links;

    /** @var ActionRegistry */
    private $actions;

    /** @var TypeFactory */
    private $types;

    /** @var TableConfigurationRegistry */
    private $config;

    /** @var ObjectListRenderer */
    private $renderer;

    protected function before() {
        $this->renderers = new RendererRegistry();
        $this->types = new TypeFactory();
        $this->links = new LinkRegistry();
        $this->actions = new ActionRegistry();
        $parser = new WebCommentParser();
        $printer = new LinkPrinter(Url::fromString('http://example.com/base'), $this->links, $this->actions, $parser);
        $this->config = new TableConfigurationRegistry();

        $this->objectRenderer = new ObjectRenderer($this->renderers, $this->types, $printer);

        $this->renderer = new ObjectListRenderer($this->renderers, $this->types, $printer, $this->config);
    }

    function requiresRendererForObjectToBeRegistered() {
        $this->try->tryTo(function () {
            $this->renderer->handles([new \DateTime(), new \DateTime()]);
        });
        $this->try->thenAnExceptionContaining_ShouldBeThrown('No Renderer found to handle <DateTime>');
    }

    function handlesArraysOfAtLeastTwoHomogeneousObjects() {
        $this->renderers->add($this->objectRenderer);
        $this->assert($this->renderer->handles([new \DateTime(), new \DateTime()]));
    }

    function doesNotHandleObjectsThatDoNotUseTheObjectRenderer() {
        $this->renderers->add(new DateTimeRenderer());
        $this->assert->not($this->renderer->handles([new \DateTime(), new \DateTime()]));
    }

    function doesNotHandleTooSmallArrays() {
        $this->renderers->add($this->objectRenderer);
        $this->assert->not($this->renderer->handles([new \DateTime()]));
        $this->assert->not($this->renderer->handles([]));
    }

    function doesNotHandleArraysWithNonNumericKeys() {
        $this->renderers->add($this->objectRenderer);
        $this->assert->not($this->renderer->handles(['foo' => new \DateTime(), 'bar' => new \DateTime()]));
    }

    function doesNotHandleHeterogeneousArrays() {
        $this->renderers->add($this->objectRenderer);
        $this->assert->not($this->renderer->handles([new \StdClass(), 'foo']));
    }

    function requiresMinimumConfiguration() {
        $this->try->tryTo(function () {
            $this->renderer->render([json_decode('{}'), json_decode('{}')]);
        });
        $this->try->thenTheException_ShouldBeThrown('No table configuration applying to <stdClass> found');
    }

    function renderTable() {
        $this->renderers->add(new PrimitiveRenderer());

        $object1 = json_decode('{"one":"uno", "two":"dos"}');
        $object2 = json_decode('{"one":"un", "two":"deux", "three":"trois"}');

        $this->config->add(new DefaultTableConfiguration($this->types));
        $this->assert($this->renderer->render([$object1, $object2]),
            '<table class="table table-striped data-table">' . "\n" .
            '<thead>' . "\n" .
            '<tr>' . "\n" .
            '<th></th>' . "\n" .
            '<th>One</th>' . "\n" .
            '<th>Two</th>' . "\n" .
            '</tr>' . "\n" .
            '</thead>' . "\n" .
            '<tr>' . "\n" .
            '<td></td>' . "\n" .
            '<td>uno</td>' . "\n" .
            '<td>dos</td>' . "\n" .
            '</tr>' . "\n" .
            '<tr>' . "\n" .
            '<td></td>' . "\n" .
            '<td>un</td>' . "\n" .
            '<td>deux</td>' . "\n" .
            '</tr>' . "\n" .
            '</table>');
    }

    function printLinks() {
        $this->actions->add('foo', Mockster::mock(Action::class));
        $this->actions->add('bar', Mockster::mock(Action::class));

        $this->links->add(new ClassLink(\StdClass::class, 'foo'));
        $this->links->add(new ClassLink(\StdClass::class, 'bar'));

        $this->config->add(new DefaultTableConfiguration($this->types));

        $this->assert->contains($this->renderer->render([json_decode('{}'), json_decode('{}')]),
            '<div class="dropdown">' . "\n" .
            '<button class="btn btn-xs btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . "\n" .
            'stdClass' . "\n" .
            '<span class="caret"></span>' . "\n" .
            '</button>' . "\n" .
            '<ul class="dropdown-menu">' . "\n" .
            '<li><a class="" href="http://example.com/base/foo"></a></li>' . "\n" .
            '<li><a class="" href="http://example.com/base/bar"></a></li>' . "\n" .
            '</ul>' . "\n" .
            '</div>');
    }

    function configureTableRendering() {
        $this->renderers->add(new PrimitiveRenderer());

        $object1 = json_decode('{"one":"uno", "two":"dos", "three":"tres"}');
        $object2 = json_decode('{"one":"un", "two":"deux", "three":"trois"}');

        $this->config->add((new GenericTableConfiguration($this->types, \StdClass::class, ['three', 'one']))
            ->setHeaderCaption('three', 'Thr33')
            ->setFilter('one', function ($in) {
                return strtoupper($in);
            }));

        $rendered = $this->renderer->render([$object1, $object2]);
        $this->assert->contains($rendered, "<th>Thr33</th>\n<th>One</th>");
        $this->assert->contains($rendered, "<td>tres</td>\n<td>UNO</td>");
    }

    function configureParentClass() {
        $this->renderers->add(new PrimitiveRenderer());
        $this->renderers->add($this->objectRenderer);
        $this->renderers->add(new MapRenderer($this->renderers));

        $this->config->add(new GenericTableConfiguration($this->types, \DateTimeInterface::class));

        $this->assert($this->renderer->render([new \DateTime(), new \DateTime()]));
    }

    function usesDataTables() {
        $this->config->add(new DefaultTableConfiguration($this->types));
        $this->renderers->add(new PrimitiveRenderer());

        $elements = $this->renderer->headElements([new \DateTime()]);
        $this->assert->contains((string)$elements[0], 'jquery.dataTables.min.css');
        $this->assert->contains((string)$elements[1], 'jquery.dataTables.min.js');
        $this->assert->contains((string)$elements[2], "$('.data-table').dataTable({");
    }

    function collectsHeadElementsFromObjectRenderer() {
        $renderer = Mockster::of(WebRenderer::class);
        Mockster::stub($renderer->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($renderer->headElements(Argument::any()))
            ->will()->return_([new Element('foo'), new Element('bar')]);

        $this->renderers->add(Mockster::mock($renderer));

        $this->config->add(new DefaultTableConfiguration($this->types));

        $elements = $this->renderer->headElements([json_decode('{"one":"uno"}')]);
        $this->assert->size($elements, 5);
        $this->assert((string)$elements[3], '<foo></foo>');
        $this->assert((string)$elements[4], '<bar></bar>');
    }
}