<?php
namespace spec\rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\web\fields\StringField;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;

class StringFieldSpec extends StaticTestSuite {

    /** @var \rtens\domin\web\WebField */
    public $field;

    protected function before() {
        $this->field = new StringField();
    }

    function handlesPrimitiveTypes() {
        $this->assert($this->field->handles(new Parameter('foo', new StringType())));

        $this->assert->not($this->field->handles(new Parameter('foo', new FloatType())));
        $this->assert->not($this->field->handles(new Parameter('foo', new UnknownType())));
        $this->assert->not($this->field->handles(new Parameter('foo', new BooleanType())));
    }

    function inflatesPrimitiveValues() {
        $param = new Parameter('foo', new StringType);
        $this->assert($this->field->inflate($param, 'foo'), 'foo');
        $this->assert($this->field->inflate($param, ''), null);
        $this->assert($this->field->inflate($param, 'some <html>'), 'some <html>');
    }

    function hasNoHeadElements() {
        $this->assert->size($this->field->headElements(new Parameter('foo', new StringType())), 0);
    }

    function emptyField() {
        $this->assert($this->field->render(new Parameter('foo', new StringType()), null),
            '<input class="form-control" type="text" name="foo" value=""/>');
    }

    function requiredField() {
        $this->assert($this->field->render(new Parameter('bar', new StringType(), true), null),
            '<input class="form-control" type="text" name="bar" value="" required="required"/>');
    }

    function withValue() {
        $this->assert($this->field->render(new Parameter('foo', new StringType()), 'some stuff'),
            '<input class="form-control" type="text" name="foo" value="some stuff"/>');
    }
}