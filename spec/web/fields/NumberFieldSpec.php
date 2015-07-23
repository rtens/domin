<?php
namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\Parameter;
use rtens\domin\delivery\web\fields\NumberField;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\DoubleType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\LongType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;

class NumberFieldSpec extends StaticTestSuite {

    /** @var \rtens\domin\delivery\web\WebField */
    public $field;

    protected function before() {
        $this->field = new NumberField();
    }

    function handlesPrimitiveTypes() {
        $this->assert($this->field->handles(new Parameter('foo', new LongType())));
        $this->assert($this->field->handles(new Parameter('foo', new FloatType())));
        $this->assert($this->field->handles(new Parameter('foo', new DoubleType())));
        $this->assert($this->field->handles(new Parameter('foo', new IntegerType())));

        $this->assert->not($this->field->handles(new Parameter('foo', new StringType())));
        $this->assert->not($this->field->handles(new Parameter('foo', new UnknownType())));
        $this->assert->not($this->field->handles(new Parameter('foo', new BooleanType())));
    }

    function inflatesPrimitiveValues() {
        $param = new Parameter('foo', new IntegerType());
        $this->assert($this->field->inflate($param, '1A'), 1);
        $this->assert($this->field->inflate($param, ''), 0);
        $this->assert($this->field->inflate($param, 'foo'), 0);
    }

    function hasNoHeadElements() {
        $this->assert->size($this->field->headElements(new Parameter('foo', new StringType())), 0);
    }

    function emptyField() {
        $this->assert($this->field->render(new Parameter('foo', new FloatType(), true), null),
            '<input class="form-control" type="text" name="foo" value="0"/>');
    }

    function useNumberInput() {
        $parameter = new Parameter('foo', new IntegerType());
        $this->assert($this->field->render($parameter, null),
            '<input class="form-control" type="number" name="foo" value="0"/>');
    }

    function withValue() {
        $this->assert($this->field->render(new Parameter('foo', new UnknownType()), 1234),
            '<input class="form-control" type="text" name="foo" value="1234"/>');
    }
}