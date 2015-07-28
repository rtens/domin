<?php
namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\delivery\web\HeadElements;
use rtens\domin\Parameter;
use rtens\domin\delivery\web\fields\BooleanField;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\StringType;

class BooleanFieldSpec extends StaticTestSuite {

    /** @var Parameter */
    private $param;

    /** @var BooleanField */
    private $field;

    protected function before() {
        $this->field = new BooleanField();
        $this->param = new Parameter('foo', new BooleanType());
    }

    function handlesBooleans() {
        $this->assert($this->field->handles($this->param));
        $this->assert->not($this->field->handles(new Parameter('foo', new StringType())));
    }

    function inflatesToBoolean() {
        $this->assert($this->field->inflate($this->param, 1) === true);
        $this->assert($this->field->inflate($this->param, '') === false);
    }

    function noHeadElements() {
        $elements = $this->field->headElements($this->param);
        $this->assert($elements[0], HeadElements::bootstrap());
        $this->assert($elements[1], HeadElements::jquery());
        $this->assert->contains((string)$elements[2], 'bootstrap-switch.min.css');
        $this->assert->contains((string)$elements[3], 'bootstrap-switch.min.js');
        $this->assert->contains((string)$elements[4], "$('.boolean-switch').bootstrapSwitch(");
    }

    function renderUnchecked() {
        $this->assert($this->field->render($this->param, false),
            '<input type="hidden" name="foo" value="0"/>' .
            '<input class="boolean-switch" type="checkbox" name="foo" value="1"/>');
    }

    function renderChecked() {
        $this->assert($this->field->render($this->param, true),
            '<input type="hidden" name="foo" value="0"/>' .
            '<input class="boolean-switch" type="checkbox" name="foo" value="1" checked="checked"/>');
    }
}