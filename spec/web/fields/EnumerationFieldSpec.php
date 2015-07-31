<?php
namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\web\fields\DateTimeField;
use rtens\domin\delivery\web\fields\EnumerationField;
use rtens\domin\Parameter;
use rtens\domin\reflection\types\EnumerationType;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;

class EnumerationFieldSpec extends StaticTestSuite {

    /** @var FieldRegistry */
    private $fields;

    /** @var EnumerationField */
    private $field;

    protected function before() {
        $this->fields = new FieldRegistry();
        $this->field = new EnumerationField($this->fields);
    }

    function handlesEnumerationType() {
        $this->assert($this->field->handles(new Parameter('foo', new EnumerationType([], new UnknownType()))));
        $this->assert->not($this->field->handles(new Parameter('foo', new StringType())));
    }

    function inflatesUsingOptionType() {
        $this->fields->add(new DateTimeField());

        $type = new EnumerationType([new \DateTime()], new ClassType(\DateTime::class));
        $this->assert($this->field->inflate(new Parameter('foo', $type), '2011-12-13'), new \DateTime('2011-12-13 00:00:00'));
    }

    function renderOptions() {
        $parameter = new Parameter('foo', new EnumerationType(['foo', 'bar'], new StringType()));
        $this->assert($this->field->render($parameter, null),
            '<select name="foo" class="form-control">' . "\n" .
            '<option value="foo">Foo</option>' . "\n" .
            '<option value="bar">Bar</option>' . "\n" .
            '</select>');
    }

    function renderWithSelectedOption() {
        $parameter = new Parameter('foo', new EnumerationType(['foo', 'bar', 'baz'], new StringType()));
        $this->assert($this->field->render($parameter, 'bar'),
            '<select name="foo" class="form-control">' . "\n" .
            '<option value="foo">Foo</option>' . "\n" .
            '<option value="bar" selected="selected">Bar</option>' . "\n" .
            '<option value="baz">Baz</option>' . "\n" .
            '</select>');
    }
}