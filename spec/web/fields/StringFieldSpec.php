<?php
namespace spec\rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\web\fields\StringField;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;

class StringFieldSpec extends StaticTestSuite {

    /** @var \rtens\domin\web\WebField */
    public $field;

    protected function before() {
        $this->field = new StringField();
    }

    function handlesOnlyStringTypes() {
        $this->assert($this->field->handles(new Parameter('foo', new StringType())));
        $this->assert->not($this->field->handles(new Parameter('foo', new UnknownType())));
        $this->assert->not($this->field->handles(new Parameter('foo', new IntegerType())));
    }

    function inflatesNonEmptyStrings() {
        $this->assert($this->field->inflate('foo'), 'foo');
        $this->assert($this->field->inflate(''), null);
    }

    function hasNoHeadElements() {
        $this->assert->size($this->field->headElements(new Parameter('foo', new StringType())), 0);
    }

    function emptyField() {
        $this->assert($this->field->render(new Parameter('foo', new StringType()), null),
            '<input type="text" name="foo" value=""/>');
    }

    function requiredField() {
        $this->assert($this->field->render(new Parameter('bar', new StringType(), true), null),
            '<input type="text" name="bar" value="" required="required"/>');
    }

    function withValue() {
        $this->assert($this->field->render(new Parameter('foo', new StringType()), 'some stuff'),
            '<input type="text" name="foo" value="some stuff"/>');
    }
}