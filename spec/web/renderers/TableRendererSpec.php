<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\DateTimeRenderer;
use rtens\domin\delivery\web\renderers\ElementRenderer;
use rtens\domin\delivery\web\renderers\link\ClassLink;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\link\LinkRegistry;
use rtens\domin\delivery\web\renderers\ObjectRenderer;
use rtens\domin\delivery\web\renderers\PrimitiveRenderer;
use rtens\domin\delivery\web\renderers\tables\ArrayTable;
use rtens\domin\delivery\web\renderers\tables\DataTable;
use rtens\domin\delivery\web\renderers\tables\DataTableRenderer;
use rtens\domin\delivery\web\renderers\tables\ObjectTable;
use rtens\domin\delivery\web\renderers\tables\Table;
use rtens\domin\delivery\web\renderers\tables\TableRenderer;
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
class TableRendererSpec extends StaticTestSuite {

    /** @var ObjectRenderer */
    private $objectRenderer;

    /** @var RendererRegistry */
    private $renderers;

    /** @var LinkRegistry */
    private $links;

    /** @var ActionRegistry */
    private $actions;

    /** @var TypeFactory */
    private $types;

    /** @var TableRenderer */
    private $tableRenderer;

    /** @var LinkPrinter */
    private $printer;

    protected function before() {
        $this->renderers = new RendererRegistry();
        $this->types = new TypeFactory();
        $this->links = new LinkRegistry();
        $this->actions = new ActionRegistry();
        $parser = new WebCommentParser();
        $this->printer = new LinkPrinter(Url::fromString('http://example.com/base'), $this->links, $this->actions, $parser);

        $this->objectRenderer = new ObjectRenderer($this->renderers, $this->types, $this->printer);

        $this->tableRenderer = new TableRenderer($this->renderers, $this->printer);
    }

    function handlesTable() {
        $this->assert($this->tableRenderer->handles(Mockster::mock(Table::class)));
        $this->assert->not($this->tableRenderer->handles('foo'));
    }

    function renderArrayTable() {
        $this->renderers->add(new PrimitiveRenderer());

        $rendered = $this->tableRenderer->render(new ArrayTable([
            [
                'one' => 'uno',
                'two' => 'dos'
            ], [
                'one' => 'un',
                'two' => 'deux'
            ]
        ]));

        $this->assert($rendered,
            '<table class="table table-striped">' . "\n" .
            '<thead>' . "\n" .
            '<tr>' . "\n" .
            '<th>One</th>' . "\n" .
            '<th>Two</th>' . "\n" .
            '</tr>' . "\n" .
            '</thead>' . "\n" .
            '<tr>' . "\n" .
            '<td>uno</td>' . "\n" .
            '<td>dos</td>' . "\n" .
            '</tr>' . "\n" .
            '<tr>' . "\n" .
            '<td>un</td>' . "\n" .
            '<td>deux</td>' . "\n" .
            '</tr>' . "\n" .
            '</table>');
    }

    function renderArrayWithInconsistentKeys() {
        $this->renderers->add(new PrimitiveRenderer());

        $rendered = $this->tableRenderer->render(new ArrayTable([
            [
                'one' => 'uno',
                'two' => 'dos'
            ], [
                'one' => 'un',
                'three' => 'trois'
            ]
        ]));

        $this->assert($rendered,
            '<table class="table table-striped">' . "\n" .
            '<thead>' . "\n" .
            '<tr>' . "\n" .
            '<th>One</th>' . "\n" .
            '<th>Two</th>' . "\n" .
            '<th>Three</th>' . "\n" .
            '</tr>' . "\n" .
            '</thead>' . "\n" .
            '<tr>' . "\n" .
            '<td>uno</td>' . "\n" .
            '<td>dos</td>' . "\n" .
            '<td></td>' . "\n" .
            '</tr>' . "\n" .
            '<tr>' . "\n" .
            '<td>un</td>' . "\n" .
            '<td></td>' . "\n" .
            '<td>trois</td>' . "\n" .
            '</tr>' . "\n" .
            '</table>');
    }

    function renderCells() {
        $this->renderers->add(new DateTimeRenderer());
        $this->renderers->add(new PrimitiveRenderer());

        $rendered = $this->tableRenderer->render(new ArrayTable([
            ['one' => new \DateTime('2011-12-13')]
        ]));

        $this->assert->contains($rendered, '2011-12-13 00:00:00');
    }

    function collectsHeadElementsFromCellRenderers() {
        $renderer = Mockster::of(WebRenderer::class);
        Mockster::stub($renderer->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($renderer->headElements(Argument::any()))
            ->will()->return_([new Element('foo'), new Element('bar')]);

        $this->renderers->add(Mockster::mock($renderer));

        $elements = $this->tableRenderer->headElements(new ArrayTable([
            ['uno' => 'one']
        ]));

        $this->assert->size($elements, 2);
        $this->assert((string)$elements[0], '<foo></foo>');
        $this->assert((string)$elements[1], '<bar></bar>');
    }

    function renderEmptyObjectTable() {
        $rendered = $this->tableRenderer->render(new ObjectTable([], $this->types));
        $this->assert($rendered, '<table class="table table-striped"><thead><tr><th></th></tr></thead></table>');
    }

    function renderObjectTable() {
        $this->renderers->add(new ElementRenderer());
        $this->renderers->add(new PrimitiveRenderer());

        $rendered = $this->tableRenderer->render(new ObjectTable([
            json_decode('{"one":"uno", "two":"dos"}'),
            json_decode('{"one":"un", "two":"deux", "three":"trois"}'),
        ], $this->types));

        $this->assert($rendered,
            '<table class="table table-striped">' . "\n" .
            '<thead>' . "\n" .
            '<tr>' . "\n" .
            '<th></th>' . "\n" .
            '<th>One</th>' . "\n" .
            '<th>Two</th>' . "\n" .
            '</tr>' . "\n" .
            '</thead>' . "\n" .
            '<tr>' . "\n" .
            '<td><div></div></td>' . "\n" .
            '<td>uno</td>' . "\n" .
            '<td>dos</td>' . "\n" .
            '</tr>' . "\n" .
            '<tr>' . "\n" .
            '<td><div></div></td>' . "\n" .
            '<td>un</td>' . "\n" .
            '<td>deux</td>' . "\n" .
            '</tr>' . "\n" .
            '</table>');
    }

    function printLinks() {
        $this->renderers->add(new ElementRenderer());

        $this->actions->add('foo', Mockster::mock(Action::class));
        $this->actions->add('bar', Mockster::mock(Action::class));

        $this->links->add(new ClassLink(\StdClass::class, 'foo'));
        $this->links->add(new ClassLink(\StdClass::class, 'bar'));

        $rendered = $this->tableRenderer->render(new ObjectTable([
            new \StdClass()
        ], $this->types));

        $this->assert->contains($rendered,
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
        $this->renderers->add(new ElementRenderer());
        $this->renderers->add(new PrimitiveRenderer());

        $data = [
            json_decode('{"one":"uno","two":"dos","three":"tres"}'),
            json_decode('{"one":"un","two":"deux","three":"trois"}'),
        ];

        $table = (new ObjectTable($data, $this->types))
            ->selectProperties(['one', 'three'])
            ->setHeader('one', '1')
            ->setFilter('three', function ($s) {
                return strtoupper($s);
            });

        $this->assert->contains($this->tableRenderer->render($table), "<th>1</th>\n<th>Three</th>");
        $this->assert->contains($this->tableRenderer->render($table), "<td>uno</td>\n<td>TRES</td>");
        $this->assert->contains($this->tableRenderer->render($table), "<td>un</td>\n<td>TROIS</td>");
    }

    function renderDataTables() {
        $table = new DataTable(Mockster::mock(Table::class));
        $tableRenderer = new DataTableRenderer($this->renderers, $this->printer);

        $this->assert($tableRenderer->handles($table));
        $this->assert->not($tableRenderer->handles(new ArrayTable([])));

        $rendered = $tableRenderer->render($table);
        $this->assert->contains((string)$rendered, '<table class="table table-striped">');

        $elements = $tableRenderer->headElements($table);
        $this->assert->contains((string)$elements[0], 'jquery.dataTables.min.css');
        $this->assert->contains((string)$elements[1], 'jquery.dataTables.min.js');
        $this->assert->contains((string)$elements[2], "$('.data-table table').dataTable({");
    }

}