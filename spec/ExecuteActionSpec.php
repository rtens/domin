<?php
namespace spec\rtens\domin;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\Field;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\execution\ExecutionResult;
use rtens\domin\Executor;
use rtens\domin\execution\FailedResult;
use rtens\domin\execution\NoResult;
use rtens\domin\execution\RenderedResult;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\collections\Map;

class ExecuteActionSpec extends StaticTestSuite {

    function unregisteredAction() {
        $this->whenIExecute('foo');
        $this->thenItShouldResultInAnError('Action [foo] is not registered.');
    }

    function emptyAction() {
        $this->givenTheAction('foo');

        $this->whenIExecute('foo');
        $this->thenThereShouldBeNoResult();
    }

    function noMatchingRenderer() {
        $this->givenTheAction_Returning('foo', 'bar');
        $this->whenIExecute('foo');
        $this->thenItShouldResultInAnError("No Renderer found to handle 'bar'");
    }

    function renderResult() {
        $this->givenTheAction_Returning('foo', 'this is foo');
        $this->givenTheRenderer(function ($in) {
            return $in . ' rendered';
        });

        $this->whenIExecute('foo');
        $this->thenTheResultShouldBe('this is foo rendered');
    }

    function noMatchingField() {
        $this->givenTheAction('foo');
        $this->given_HasTheParameter('foo', 'one');

        $this->whenIExecute('foo');
        $this->thenItShouldResultInAnError('No field found to handle [type of one]');
    }

    function inflateParameters() {
        $this->givenTheAction('foo');
        $this->given_ExecutesWith('foo', function (Map $params) {
            return $params->asList()->join(' ');
        });
        $this->given_HasTheParameter('foo', 'one');
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

    function chooseFieldForParameterType() {
        $this->givenTheAction('foo');
        $this->given_ExecutesWith('foo', function (Map $params) {
            return $params->asList()->join(' ');
        });
        $this->given_HasTheParameter_OfType('foo', 'one', 'bar');
        $this->given_HasTheParameter_OfType('foo', 'two', 'bas');
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
        $this->givenTheAction_Returning('foo', 'this is foo');
        $this->givenTheAction_Returning('bar', 'this is bar');

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

    /** @var Mockster[]|Action[] */
    private $actions = [];

    /** @var Mockster[]|Renderer[] */
    private $renderers = [];

    /** @var Mockster[]|Field[] */
    private $fields = [];

    /** @var Map[] */
    private $params = [];

    /** @var ParameterReader|Mockster */
    private $reader;

    /** @var ExecutionResult|RenderedResult|FailedResult */
    private $result;

    protected function before() {
        $this->reader = new Mockster(ParameterReader::class);
    }

    private function givenTheAction($id) {
        $this->givenTheAction_Returning($id, null);
    }

    private function givenTheAction_Returning($id, $value) {
        $this->actions[$id] = new Mockster(Action::class);

        $this->params[$id] = new Map();
        Mockster::stub($this->actions[$id]->parameters())->will()->return_($this->params[$id]);
        Mockster::stub($this->actions[$id]->execute(Argument::any()))->will()->return_($value);
    }

    private function given_ExecutesWith($id, $callback) {
        Mockster::stub($this->actions[$id]->execute(Argument::any()))->will()->forwardTo($callback);
    }

    private function given_HasTheParameter($id, $name) {
        $this->given_HasTheParameter_OfType($id, $name, "type of $name");
    }

    private function given_HasTheParameter_OfType($id, $name, $type) {
        $this->params[$id]->set($name, $type);
    }

    private function givenAFieldInflatingWith($callback) {
        $this->givenAFieldHandling_InflatingWith(Argument::any(), $callback);
    }

    private function givenAFieldHandling_InflatingWith($type, $callback) {
        /** @var Field|Mockster $field */
        $field = new Mockster(Field::class);
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
        /** @var Mockster|Renderer $renderer */
        $renderer = new Mockster(Renderer::class);
        $this->renderers[] = $renderer;

        Mockster::stub($renderer->handles($value))->will()->return_(true);
        Mockster::stub($renderer->render(Argument::any()))->will()->forwardTo($callback);
    }

    private function whenIExecute($id) {
        $actions = new ActionRegistry();
        foreach ($this->actions as $actionId => $action) {
            $actions->add($actionId, $action->mock());
        }

        $fields = new FieldRegistry();
        foreach ($this->fields as $field) {
            $fields->add($field->mock());
        }

        $renderers = new RendererRegistry();
        foreach ($this->renderers as $renderer) {
            $renderers->add($renderer->mock());
        }

        $this->result = (new Executor($actions, $fields, $renderers, $this->reader->mock()))->execute($id);
    }

    private function thenTheResultShouldBe($value) {
        $this->assert->isInstanceOf($this->result, RenderedResult::class);
        $this->assert($this->result->getOutput(), $value);
    }

    private function thenThereShouldBeNoResult() {
        $this->assert->isInstanceOf($this->result, NoResult::class);
    }

    private function thenItShouldResultInAnError($message) {
        $this->assert->isInstanceOf($this->result, FailedResult::class);
        $this->assert($this->result->getMessage(), $message);
    }
}