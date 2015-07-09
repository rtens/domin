<?php
namespace spec\rtens\domin;

use rtens\domin\delivery\Field;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\execution\ExecutionResult;
use rtens\domin\execution\FailedResult;
use rtens\domin\execution\MissingParametersResult;
use rtens\domin\execution\NoResult;
use rtens\domin\execution\RenderedResult;
use rtens\domin\Executor;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

/**
 * @property \spec\rtens\domin\fixtures\ActionFixture action <-
 */
class ExecuteActionSpec extends StaticTestSuite {

    function unregisteredAction() {
        $this->whenIExecute('foo');
        $this->thenTheResultShouldBeTheError('Action [foo] is not registered.');
    }

    function emptyAction() {
        $this->action->givenTheAction('foo');

        $this->whenIExecute('foo');
        $this->thenThereShouldBeNoResult();
    }

    function noMatchingRenderer() {
        $this->action->givenTheAction_Returning('foo', 'bar');
        $this->whenIExecute('foo');
        $this->thenTheResultShouldBeTheError("No Renderer found to handle 'bar'");
    }

    function renderResult() {
        $this->action->givenTheAction_Returning('foo', 'this is foo');
        $this->givenTheRenderer(function ($in) {
            return $in . ' rendered';
        });

        $this->whenIExecute('foo');
        $this->thenTheResultShouldBe('this is foo rendered');
    }

    function noMatchingField() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->givenTheParameter_Is('one', 'uno');

        $this->whenIExecute('foo');
        $this->thenTheResultShouldBeTheError('No field found to handle [type of one]');
    }

    function inflateParameters() {
        $this->action->givenTheAction('foo');
        $this->action->given_ExecutesWith('foo', function ($params) {
            return implode(' ', $params);
        });
        $this->action->given_HasTheParameter('foo', 'one');
        $this->givenAFieldInflatingWith(function ($s) {
            return $s . '!';
        });
        $this->givenTheRenderer(function ($in) {
            return $in . ' rendered';
        });

        $this->givenTheParameter_Is('one', 'uno');

        $this->whenIExecute('foo');
        $this->thenTheResultShouldBe('uno! rendered');
    }

    function checkForMissingParameters() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->action->given_HasTheRequiredParameter('foo', 'two');
        $this->action->given_HasTheRequiredParameter('foo', 'three');
        $this->action->given_HasTheRequiredParameter('foo', 'four');

        $this->givenTheParameter_Is('three', 'tres');
        $this->givenAFieldHandling_InflatingWith('type of three', function ($s) {
            return $s . '!';
        });

        $this->whenIExecute('foo');
        $this->thenTheResultShouldBeThatParameters_AreMissing(['two', 'four']);
    }

    function chooseFieldForParameterType() {
        $this->action->givenTheAction('foo');
        $this->action->given_ExecutesWith('foo', function ($params) {
            return implode(' ', $params);
        });
        $this->action->given_HasTheParameter_OfType('foo', 'one', 'bar');
        $this->action->given_HasTheParameter_OfType('foo', 'two', 'bas');
        $this->givenAFieldHandling_InflatingWith('bar', function ($s) {
            return $s . '?';
        });
        $this->givenAFieldHandling_InflatingWith('bas', function ($s) {
            return $s . '!';
        });
        $this->givenTheRenderer(function ($in) {
            return $in . ' rendered';
        });

        $this->givenTheParameter_Is('one', 'uno');
        $this->givenTheParameter_Is('two', 'dos');

        $this->whenIExecute('foo');
        $this->thenTheResultShouldBe('uno? dos! rendered');
    }

    function chooseRendererForReturnedValue() {
        $this->action->givenTheAction_Returning('foo', 'this is foo');
        $this->action->givenTheAction_Returning('bar', 'this is bar');

        $this->givenARendererFor_RenderingWith('this is foo', function ($s) {
            return $s . ' with foo';
        });
        $this->givenARendererFor_RenderingWith('this is bar', function ($s) {
            return $s . ' with bar';
        });

        $this->whenIExecute('foo');
        $this->thenTheResultShouldBe('this is foo with foo');

        $this->whenIExecute('bar');
        $this->thenTheResultShouldBe('this is bar with bar');
    }

    /** @var Renderer[] */
    private $renderers = [];

    /** @var Field[] */
    private $fields = [];

    /** @var ParameterReader */
    private $reader;

    /** @var ExecutionResult|RenderedResult|FailedResult|MissingParametersResult */
    private $result;

    protected function before() {
        $this->reader = Mockster::of(ParameterReader::class);
    }

    private function givenAFieldInflatingWith($callback) {
        $this->givenAFieldHandling_InflatingWith(Argument::any(), $callback);
    }

    private function givenAFieldHandling_InflatingWith($type, $callback) {
        $field = Mockster::of(Field::class);
        $this->fields[] = $field;

        Mockster::stub($field->handles($type))->will()->return_(true);
        Mockster::stub($field->inflate(Argument::any()))->will()->forwardTo($callback);
    }

    private function givenTheParameter_Is($key, $value) {
        Mockster::stub($this->reader->read($key))->will()->return_($value);
    }

    private function givenTheRenderer($callback) {
        $this->givenARendererFor_RenderingWith(Argument::any(), $callback);
    }

    private function givenARendererFor_RenderingWith($value, $callback) {
        $renderer = Mockster::of(Renderer::class);
        $this->renderers[] = $renderer;

        Mockster::stub($renderer->handles($value))->will()->return_(true);
        Mockster::stub($renderer->render(Argument::any()))->will()->forwardTo($callback);
    }

    private function whenIExecute($id) {
        $fields = new FieldRegistry();
        foreach ($this->fields as $field) {
            $fields->add(Mockster::mock($field));
        }

        $renderers = new RendererRegistry();
        foreach ($this->renderers as $renderer) {
            $renderers->add(Mockster::mock($renderer));
        }

        $executor = new Executor($this->action->registry, $fields, $renderers, Mockster::mock($this->reader));
        $this->result = $executor->execute($id);
    }

    private function thenTheResultShouldBe($value) {
        $this->assert->isInstanceOf($this->result, RenderedResult::class);
        $this->assert($this->result->getOutput(), $value);
    }

    private function thenThereShouldBeNoResult() {
        $this->assert->isInstanceOf($this->result, NoResult::class);
    }

    private function thenTheResultShouldBeTheError($message) {
        $this->assert->isInstanceOf($this->result, FailedResult::class);
        $this->assert($this->result->getMessage(), $message);
    }

    private function thenTheResultShouldBeThatParameters_AreMissing($names) {
        $this->assert->isInstanceOf($this->result, MissingParametersResult::class);
        $this->assert($this->result->getParameters(), $names);

    }
}