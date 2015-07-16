<?php
namespace spec\rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\web\fields\PrimitiveField;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\DoubleType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\LongType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;

class PrimitiveFieldSpec extends StaticTestSuite {

    /** @var \rtens\domin\web\WebField */
    public $field;

    protected function before() {
        $this->field = new PrimitiveField();
    }

    function handlesPrimitiveTypes() {
        $this->assert($this->field->handles(new Parameter('foo', new StringType())));
        $this->assert($this->field->handles(new Parameter('foo', new LongType())));
        $this->assert($this->field->handles(new Parameter('foo', new FloatType())));
        $this->assert($this->field->handles(new Parameter('foo', new DoubleType())));
        $this->assert($this->field->handles(new Parameter('foo', new IntegerType())));

        $this->assert->not($this->field->handles(new Parameter('foo', new UnknownType())));
        $this->assert->not($this->field->handles(new Parameter('foo', new BooleanType())));
    }

    function inflatesNonEmptyStrings() {
        $param = new Parameter('foo', new StringType);
        $this->assert($this->field->inflate($param, 'foo'), 'foo');
        $this->assert($this->field->inflate($param, ''), null);
    }

    function useNumberInput() {
        $parameter = new Parameter('foo', new IntegerType());
        $this->assert($this->field->inflate($parameter, '12') === 12);
        $this->assert($this->field->render($parameter, null),
            '<input class="form-control" type="number" name="foo" value=""/>');
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