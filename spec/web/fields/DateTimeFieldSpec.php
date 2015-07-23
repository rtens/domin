<?php
namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\Parameter;
use rtens\domin\delivery\web\fields\DateTimeField;
use rtens\domin\delivery\web\HeadElements;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\UnknownType;

class DateTimeFieldSpec extends StaticTestSuite {

    /** @var DateTimeFieldSpec_DateTimeField */
    private $field;

    protected function before() {
        $this->field = new DateTimeFieldSpec_DateTimeField();
    }

    function handlesDateTimeInterface() {
        $this->assert($this->field->handles(new Parameter('foo', new ClassType(\DateTime::class))));
        $this->assert($this->field->handles(new Parameter('foo', new ClassType(\DateTimeImmutable::class))));
        $this->assert->not($this->field->handles(new Parameter('foo', new ClassType(\StdClass::class))));
    }

    function inflateToObject() {
        $this->assert->isInstanceOf($this->field->inflate(new Parameter('foo', new ClassType(\DateTime::class)), 'yesterday'),
            \DateTime::class);
        $this->assert->isInstanceOf($this->field->inflate(new Parameter('foo', new ClassType(\DateTimeImmutable::class)), 'yesterday'),
            \DateTimeImmutable::class);
    }

    function rendersControl() {
        $this->assert($this->field->render(new Parameter('foo', new UnknownType()), null),
            '<div class="input-group date datetimepicker" style="width: 100%;">' . "\n" .
            '<span class="input-group-addon" onclick="$(this).parents(\'.datetimepicker\').datetimepicker(dateTimePickerSettings); $(this).siblings(\'.hidden\').toggleClass(\'hidden\'); $(this).remove(); return false;">' .
            '<span class="glyphicon glyphicon-calendar" style="opacity: 0.5"></span>' .
            '</span>' . "\n" .
            '<span class="input-group-addon hidden"><span class="glyphicon glyphicon-calendar"></span></span>' . "\n" .
            '<input type="text" name="foo" class="form-control" value=""/>' . "\n" .
            '</div>');
    }

    function rendersRequiredControl() {
        $this->assert->contains($this->field->render(new Parameter('foo', new UnknownType(), true), null),
            '<input type="text" name="foo" class="form-control" value="" required="required"/>');
    }

    function rendersValue() {
        $this->assert->contains($this->field->render(new Parameter('foo', new UnknownType()), new \DateTime('2011-12-13 14:15:16')),
            '<input type="text" name="foo" class="form-control" value="2011-12-13 14:15:16"/>');
    }

    function requiresLibraries() {
        $headElements = implode('', $this->field->headElements(new Parameter('foo', new UnknownType())));
        $this->assert->contains($headElements, (string)HeadElements::jquery());
        $this->assert->contains($headElements, (string)HeadElements::bootstrap());
        $this->assert->contains($headElements, (string)HeadElements::bootstrapJs());
        $this->assert->contains($headElements, 'moment.js');
        $this->assert->contains($headElements, 'bootstrap-datetimepicker.min.js');
        $this->assert->contains($headElements, 'bootstrap-datetimepicker.min.css');
    }

    function readsOptions() {
        $this->field->options = ['key' => ['value']];

        $headElements = implode('', $this->field->headElements(new Parameter('foo', new UnknownType())));
        $this->assert->contains($headElements, 'var dateTimePickerSettings = {"key":["value"]};');
    }
}

class DateTimeFieldSpec_DateTimeField extends DateTimeField {
    public $options = [];
    protected function getOptions() {
        return $this->options;
    }
}