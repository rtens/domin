<?php
namespace spec\rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\web\fields\BooleanField;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\StringType;

class BooleanFieldSpec extends StaticTestSuite {

    /** @var BooleanField */
    private $field;

    protected function before() {
        $this->field = new BooleanField();
    }

    function handlesBooleans() {
        $this->assert($this->field->handles(new Parameter('foo', new BooleanType())));
        $this->assert->not($this->field->handles(new Parameter('foo', new StringType())));
    }

    function inflatesToBoolean() {
        $this->assert($this->field->inflate(1) === true);
        $this->assert($this->field->inflate('') === false);
    }

    function noHeadElements() {
        $this->assert($this->field->headElements(new Parameter('foo', new StringType())), []);
    }

    function renderUnchecked() {
        $this->assert($this->field->render(new Parameter('foo', new StringType()), false),
            '<input type="hidden" name="foo" value="0"/>' .
            '<input type="checkbox" name="foo" value="1"/>');
    }

    function renderChecked() {
        $this->assert($this->field->render(new Parameter('foo', new StringType()), true),
            '<input type="hidden" name="foo" value="0"/>' .
            '<input type="checkbox" name="foo" value="1" checked="checked"/>');
    }
}