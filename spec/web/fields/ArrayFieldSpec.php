<?php
namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\web\MobileDetector;
use rtens\domin\Parameter;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\fields\ArrayField;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebField;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\collections\Liste;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;

class ArrayFieldSpec extends StaticTestSuite {

    /** @var WebField */
    public $mockster;

    /** @var ArrayField */
    private $field;

    /** @var MobileDetector */
    private $detector;

    protected function before() {
        $fields = new FieldRegistry();
        $this->mockster = Mockster::of(WebField::class);
        Mockster::stub($this->mockster->handles(Argument::any()))->will()->return_(true);

        $this->detector = Mockster::of(MobileDetector::class);

        $fields->add(Mockster::mock($this->mockster));
        $this->field = new ArrayField($fields, Mockster::mock($this->detector));
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
        $this->assert($this->field->inflate($param, new Liste(['ignored', 'one', 'two'])),
            ['foo_one', 'foo_two']);
    }

    function requiredScripts() {
        $this->assert($this->field->headElements(new Parameter('foo', new ArrayType(new UnknownType()))), [
            HeadElements::jquery(),
            HeadElements::jqueryUi(),
            new Element('script', [], [
                "$(function () {
                    $('.array-new-items').appendTo('body');
                    $('.array-items').sortable({handle:'.sortable-handle'});
                    $('.array-items .sortable-handle').disableSelection();
                });"
            ])
        ]);
    }

    function requireTouchPunchForMobile() {
        Mockster::stub($this->detector->isMobile())->will()->return_(true);
        $this->assert->contains($this->field->headElements(new Parameter('foo', new ArrayType(new UnknownType()))),
            HeadElements::script('//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js'));
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
            '<div id="foo-items" class="array-items">' .
            '<input type="hidden" name="foo[0]" value="' . ArrayField::EMPTY_LIST_VALUE . '"/>' .
            '</div>' . "\n" .
            '<button class="btn btn-success" onclick="$(\'#foo-new-items\').children().first().appendTo(\'#foo-items\'); return false;">Add</button>' . "\n" .
            '<div id="foo-new-items" class="array-new-items hidden">' . "\n" .
            '<div class="array-item form-group input-group">' . "\n" .
            '<span class="sortable-handle input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>' . "\n" .
            '-- foo[1] --' . "\n" .
            '<span class="input-group-btn"><button class="btn btn-danger" onclick="$(this).closest(\'.array-item\').prependTo(\'#foo-new-items\'); return false;">X</button></span>' . "\n" .
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
            '<span class="sortable-handle input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>' . "\n" .
            '-- foo[1]: one --');
        $this->assert->contains($rendered,
            '<div class="array-item form-group input-group">' . "\n" .
            '<span class="sortable-handle input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>' . "\n" .
            '-- foo[2]: two --');
    }
} 