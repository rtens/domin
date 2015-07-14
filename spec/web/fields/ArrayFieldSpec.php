<?php
namespace spec\rtens\domin\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\web\fields\ArrayField;
use rtens\domin\web\WebField;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\collections\Liste;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\StringType;

class ArrayFieldSpec extends StaticTestSuite {

    /** @var ArrayField */
    private $field;

    protected function before() {
        $fields = new FieldRegistry();
        $field = Mockster::of(WebField::class);
        Mockster::stub($field->handles(Argument::any()))->will()->return_(true);
        Mockster::stub($field->render(Argument::any(), Argument::any()))->will()->forwardTo(function (Parameter $parameter, $value) {
            return '-- ' . $parameter->getName() . ': ' . $value . ' --';
        });

        $fields->add(Mockster::mock($field));
        $this->field = new ArrayField($fields);
    }

    function handlesArrayTypes() {
        $this->assert($this->field->handles(new Parameter('foo', new ArrayType(new StringType()))));
        $this->assert->not($this->field->handles(new Parameter('foo', new StringType())));
    }

    function inflatesListsWithOneTooMany() {
        $this->assert($this->field->inflate(new Liste(['one', 'tooMany'])), ['one']);
    }

    function noHeadElements() {
        $this->assert($this->field->headElements(new Parameter('foo', new StringType())), []);
    }

    function renderEmptyArray() {
        $parameter = new Parameter('foo', new ArrayType(new StringType()));
        $this->assert($this->field->render($parameter, []),
            '<div>' . "\n" .
            '<div id="foo-container"></div>' . "\n" .
            '<button class="btn btn-success" onclick="document.getElementById(\'foo-container\').appendChild(document.getElementById(\'foo-inner\').getElementsByTagName(\'p\')[0].cloneNode(true)); return false;">Add</button>' . "\n" .
            '<div id="foo-inner" class="hidden">' . "\n" .
            '<p class="input-group">' . "\n" .
            '-- foo[]:  --' . "\n" .
            '<span class="input-group-btn"><button class="btn btn-danger" onclick="var me = this.parentElement.parentElement; console.log(me.parentElement.removeChild(me)); return false;">X</button></span>' . "\n" .
            '</p>' . "\n" .
            '</div>' . "\n" .
            '</div>');
    }

    function renderArrayWithItems() {
        $parameter = new Parameter('foo', new ArrayType(new StringType()));
        $rendered = $this->field->render($parameter, ['one', 'two']);
        $this->assert->contains($rendered,
            '<p class="input-group">' . "\n" .
            '-- foo[]: one --' . "\n" .
            '<span class="input-group-btn"><button class="btn btn-danger" onclick="var me = this.parentElement.parentElement; console.log(me.parentElement.removeChild(me)); return false;">X</button></span>' . "\n" .
            '</p>');
        $this->assert->contains($rendered,
            '<p class="input-group">' . "\n" .
            '-- foo[]: two --' . "\n" .
            '<span class="input-group-btn"><button class="btn btn-danger" onclick="var me = this.parentElement.parentElement; console.log(me.parentElement.removeChild(me)); return false;">X</button></span>' . "\n" .
            '</p>');
    }
} 