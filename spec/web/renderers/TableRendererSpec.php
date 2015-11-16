<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\DateTimeRenderer;
use rtens\domin\delivery\web\renderers\ElementRenderer;
use rtens\domin\delivery\web\renderers\link\types\GenericLink;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\link\LinkRegistry;
use rtens\domin\delivery\web\renderers\ObjectRenderer;
use rtens\domin\delivery\web\renderers\PrimitiveRenderer;
use rtens\domin\delivery\web\renderers\tables\types\ArrayTable;
use rtens\domin\delivery\web\renderers\tables\types\DataTable;
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

        $this->tableRenderer = new TableRenderer($this->renderers, $this->printer);
    }

    function handlesTable() {
        $this->assert($this->tableRenderer->handles(Mockster::mock(Table::class)));
        $this->assert->not($this->tableRenderer->handles('foo'));
    }

    function renderEmptyTable() {
        $this->assert($this->tableRenderer->render(new ArrayTable([])), null);
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
            '<th width="1"></th>' . "\n" .
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
            '<th width="1"></th>' . "\n" .
            '<th>One</th>' . "\n" .
            '<th>Two</th>' . "\n" .
            '<th>Three</th>' . "\n" .
            '</tr>' . "\n" .
            '</thead>' . "\n" .
            '<tr>' . "\n" .
            '<td></td>' . "\n" .
            '<td>uno</td>' . "\n" .
            '<td>dos</td>' . "\n" .
            '<td></td>' . "\n" .
            '</tr>' . "\n" .
            '<tr>' . "\n" .
            '<td></td>' . "\n" .
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
        $this->renderers->add(new DataTableRenderer($this->renderers, $this->printer));
        $this->renderers->add(new TableRenderer($this->renderers, $this->printer));

        $table = new DataTable(Mockster::mock(Table::class));
        $tableRenderer = new DataTableRenderer($this->renderers, $this->printer);

        $this->assert($tableRenderer->handles($table));
        $this->assert->not($tableRenderer->handles(new ArrayTable([])));

        $rendered = $tableRenderer->render($table);
        $this->assert($rendered, null);

        $rendered = $tableRenderer->render(new DataTable(new ArrayTable(['foo' => 'bar'])));
        $this->assert->contains((string)$rendered, '<div class="data-table">');

        $elements = $tableRenderer->headElements($table);
        $this->assert->contains((string)$elements[0], 'jquery.dataTables.min.css');
        $this->assert->contains((string)$elements[1], 'jquery.dataTables.min.js');
        $this->assert->contains((string)$elements[2], "$('.data-table table').dataTable({");
    }

    function printLinks() {
        $this->renderers->add(new ElementRenderer());
        $this->renderers->add(new PrimitiveRenderer());

        $this->actions->add('foo', Mockster::mock(Action::class));
        $this->actions->add('bar', Mockster::mock(Action::class));
        $this->actions->add('baz', Mockster::mock(Action::class));

        $this->links->add(new GenericLink('foo', function ($item) {
            return $item['id'] == 'foo item';
        }));
        $this->links->add(new GenericLink('bar', function ($item) {
            return $item['id'] == 'bar item';
        }));
        $this->links->add(new GenericLink('baz', function ($item) {
            return $item['id'] == 'bar item';
        }));

        $rendered = $this->tableRenderer->render(new ArrayTable([
            ['id' => 'foo item'],
            ['id' => 'bar item'],
        ]));

        $this->assert->contains($rendered,
            '<div class="dropdown">' . "\n" .
            '<button class="btn btn-xs btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . "\n" .
            'Actions' . "\n" .
            '<span class="caret"></span>' . "\n" .
            '</button>' . "\n" .
            '<ul class="dropdown-menu">' .
            '<li><a class="" href="http://example.com/base/foo"></a></li>' .
            '</ul>' . "\n" .
            '</div>' . "\n" .
            '</td>' . "\n" .
            '<td>foo item</td>');

        $this->assert->contains($rendered,
            '<div class="dropdown">' . "\n" .
            '<button class="btn btn-xs btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . "\n" .
            'Actions' . "\n" .
            '<span class="caret"></span>' . "\n" .
            '</button>' . "\n" .
            '<ul class="dropdown-menu">' . "\n" .
            '<li><a class="" href="http://example.com/base/bar"></a></li>' . "\n" .
            '<li><a class="" href="http://example.com/base/baz"></a></li>' . "\n" .
            '</ul>' . "\n" .
            '</div>' . "\n" .
            '</td>' . "\n" .
            '<td>bar item</td>');
    }

    function configureTableRendering() {
        $this->renderers->add(new ElementRenderer());
        $this->renderers->add(new PrimitiveRenderer());

        $data = [
            ['one' => 'uno', 'two' => 'dos', 'three' => 'tres'],
            ['one' => 'un', 'two' => 'deux', 'three' => 'trois'],
        ];

        $table = (new ArrayTable($data, $this->types))
            ->selectColumns(['one', 'three'])
            ->setHeader('one', '1')
            ->setFilter('three', function ($s) {
                return strtoupper($s);
            });

        $this->assert->contains($this->tableRenderer->render($table), "<th>1</th>\n<th>Three</th>");
        $this->assert->contains($this->tableRenderer->render($table), "<td>uno</td>\n<td>TRES</td>");
        $this->assert->contains($this->tableRenderer->render($table), "<td>un</td>\n<td>TROIS</td>");
    }

    function nestedTables() {
        $this->renderers->add(new PrimitiveRenderer());
        $this->renderers->add($this->tableRenderer);

        $tableInTable = new ArrayTable([
            ['table' => new ArrayTable(['one' => 'uno', 'two' => 'dos']),]
        ]);

        $this->tableRenderer->render($tableInTable);
        $this->tableRenderer->headElements($tableInTable);

        $this->assert->pass();
    }

}