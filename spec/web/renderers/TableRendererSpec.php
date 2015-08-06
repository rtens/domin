<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\ActionRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\DateTimeRenderer;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\link\LinkRegistry;
use rtens\domin\delivery\web\renderers\ObjectRenderer;
use rtens\domin\delivery\web\renderers\PrimitiveRenderer;
use rtens\domin\delivery\web\renderers\tables\ArrayTable;
use rtens\domin\delivery\web\renderers\tables\DataTable;
use rtens\domin\delivery\web\renderers\tables\DataTableRenderer;
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

        $this->tableRenderer = new TableRenderer($this->renderers);
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

    function renderDataTables() {
        $this->renderers->add(new DataTableRenderer($this->renderers));
        $this->renderers->add(new TableRenderer($this->renderers));

        $table = new DataTable(Mockster::mock(Table::class));
        $tableRenderer = new DataTableRenderer($this->renderers);

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