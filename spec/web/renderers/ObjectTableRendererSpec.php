<?php
namespace spec\rtens\domin\delivery\web\renderers;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\renderers\ElementRenderer;
use rtens\domin\delivery\web\renderers\link\types\ClassLink;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\link\LinkRegistry;
use rtens\domin\delivery\web\renderers\PrimitiveRenderer;
use rtens\domin\delivery\web\renderers\tables\TableRenderer;
use rtens\domin\delivery\web\renderers\tables\types\ObjectTable;
use rtens\domin\delivery\web\WebCommentParser;
use rtens\domin\reflection\types\TypeFactory;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\protocol\Url;

class ObjectTableRendererSpec extends StaticTestSuite {

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

        $this->tableRenderer = new TableRenderer($this->renderers, $this->printer);
    }

    function handlesObjectTables() {
        $this->assert($this->tableRenderer->handles(new ObjectTable([], $this->types)));
    }

    function renderEmptyObjectTable() {
        $rendered = $this->tableRenderer->render(new ObjectTable([], $this->types));
        $this->assert($rendered, null);
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

    function printLinks() {
        $this->renderers->add(new ElementRenderer());
        $this->renderers->add(new PrimitiveRenderer());

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
}