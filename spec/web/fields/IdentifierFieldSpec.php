<?php
namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\web\fields\IdentifierField;
use rtens\domin\Parameter;
use rtens\domin\parameters\IdentifiersProvider;
use rtens\domin\reflection\types\IdentifierType;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;

/**
 * @property \rtens\scrut\fixtures\ExceptionFixture try <-
 */
class IdentifierFieldSpec extends StaticTestSuite {

    /** @var IdentifiersProvider */
    private $identifiers;

    /** @var IdentifierField */
    private $field;

    protected function before() {
        $this->identifiers = new IdentifiersProvider();
        $this->field = new IdentifierField(new FieldRegistry(), $this->identifiers);
    }

    function handlesIdentifierTypes() {
        $this->assert($this->field->handles(new Parameter('foo', new IdentifierType('Foo', new UnknownType()))));
        $this->assert->not($this->field->handles(new Parameter('foo', new UnknownType())));
    }

    function noProviderRegistered() {
        $this->try->tryTo(function () {
            $parameter = new Parameter('foo', new IdentifierType('Foo', new StringType()));
            $this->field->render($parameter, 'one');
        });
        $this->try->thenTheException_ShouldBeThrown('No identifier provider registered for [Foo]');
    }

    function renderOptions() {
        $this->identifiers->setProvider('Foo', function () {
            return [
                'one' => 'uno',
                'two' => 'dos',
                'three' => 'tres'
            ];
        });

        $parameter = new Parameter('foo', new IdentifierType('Foo', new StringType()));
        $this->assert($this->field->render($parameter, 'two'),
            '<select name="foo" class="form-control">' . "\n" .
            '<option value="one">Uno</option>' . "\n" .
            '<option value="two" selected="selected">Dos</option>' . "\n" .
            '<option value="three">Tres</option>' . "\n" .
            '</select>');
    }
}