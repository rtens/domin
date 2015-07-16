<?php
namespace spec\rtens\domin\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\fields\ArrayField;
use rtens\domin\web\HeadElements;
use rtens\domin\web\WebField;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\collections\Liste;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;

class ArrayFieldSpec extends StaticTestSuite {

    /** @var ArrayField */
    public $mockster;

    /** @var ArrayField */
    private $field;

    protected function before() {
        $fields = new FieldRegistry();
        $this->mockster = Mockster::of(WebField::class);
        Mockster::stub($this->mockster->handles(Argument::any()))->will()->return_(true);

        $fields->add(Mockster::mock($this->mockster));
        $this->field = new ArrayField($fields);
    }

    function handlesArrayTypes() {
        $this->assert($this->field->handles(new Parameter('foo', new ArrayType(new StringType()))));
        $this->assert->not($this->field->handles(new Parameter('foo', new StringType())));
    }

    function inflatesList() {
        Mockster::stub($this->mockster->inflate(Argument::any(), Argument::any()))->will()->forwardTo(function (Parameter $p, $v) {
            return $p->getName() . '_' . $v;
        });

        $param = new Parameter('foo', new ArrayType(new StringType()));
        $this->assert($this->field->inflate($param, new Liste(['one', 'two'])),
            ['foo[]_one', 'foo[]_two']);
    }

    function requiredScripts() {
        $this->assert($this->field->headElements(new Parameter('foo', new ArrayType(new UnknownType()))), [
            HeadElements::jquery(),
            new Element('script', [], [
                "$(function () {
                    $('.array-new-items').detach().appendTo('body');
                });"
            ])
        ]);
    }

    function requiresScriptsOfItems() {
        Mockster::stub($this->mockster->headElements(Argument::any()))->will()->return_([
            new Element('link', ['href' => 'http://example.com/foo'])
        ]);

        $this->assert->contains($this->field->headElements(new Parameter('foo', new ArrayType(new UnknownType()))),
            new Element('link', ['href' => 'http://example.com/foo']));
    }

    function renderEmptyArray() {
        Mockster::stub($this->mockster->render(Argument::any(), Argument::any()))->will()->forwardTo(function (Parameter $parameter) {
            return '-- ' . $parameter->getName() . ' --';
        });

        $parameter = new Parameter('foo', new ArrayType(new StringType()));
        $rendered = $this->field->render($parameter, []);
        $this->assert->contains($rendered,
            '<div id="foo-items"></div>' . "\n" .
            '<button class="btn btn-success" onclick="$(\'#foo-new-items\').children().first().detach().appendTo(\'#foo-items\'); return false;">Add</button>' . "\n" .
            '<div id="foo-new-items" class="array-new-items hidden">' . "\n" .
            '<div class="array-item form-group input-group">' . "\n" .
            '-- foo[] --' . "\n" .
            '<span class="input-group-btn"><button class="btn btn-danger" onclick="$(this).parents(\'.array-item\').detach().prependTo(\'#foo-new-items\'); return false;">X</button></span>' . "\n" .
            '</div>');
    }

    function convertNameForUsageAsId() {
        $parameter = new Parameter('foo[bar]', new ArrayType(new StringType()));
        $rendered = $this->field->render($parameter, []);
        $this->assert->contains($rendered, 'id="foo-bar--new-items"');
    }

    function renderArrayWithItems() {
        Mockster::stub($this->mockster->render(Argument::any(), Argument::any()))->will()->forwardTo(function (Parameter $parameter, $value) {
            return '-- ' . $parameter->getName() . ': ' . $value . ' --';
        });

        $parameter = new Parameter('foo', new ArrayType(new StringType()));
        $rendered = $this->field->render($parameter, ['one', 'two']);
        $this->assert->contains($rendered,
            '<div class="array-item form-group input-group">' . "\n" .
            '-- foo[]: one --');
        $this->assert->contains($rendered,
            '<div class="array-item form-group input-group">' . "\n" .
            '-- foo[]: two --');
    }
} 