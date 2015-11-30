<?php namespace spec\rtens\domin;

use rtens\domin\AccessControl;
use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\fields\StringField;
use rtens\domin\execution\NoResult;
use rtens\domin\execution\NotPermittedResult;
use rtens\domin\Executor;
use rtens\domin\Parameter;
use rtens\mockster\Mockster;
use watoki\reflect\type\StringType;

/**
 * @property \rtens\scrut\Assert assert <-
 * @property \rtens\scrut\fixtures\ExceptionFixture try <-
 */
class ControlExecutionPermissionsSpec {

    function doNotListInvisibleAction() {
        $this->givenTheInvisibleActions(['foo', 'bar']);
        $this->given_IsVisible('foo');

        $actions = $this->registry->getAllActions();

        $this->assert->size($actions, 1);
        $this->assert->contains(array_keys($actions), 'foo');
    }

    function doNotGetInvisibleAction() {
        $this->givenTheInvisibleActions(['foo', 'bar']);
        $this->given_IsVisible('foo');

        $this->registry->getAction('foo');
        $this->try->tryTo(function () {
            $this->registry->getAction('bar');
        });

        $this->try->thenTheException_ShouldBeThrown('Action [bar] is not registered.');
    }

    function denyNotPermittedExecutions() {
        $this->givenTheInvisibleActions(['foo', 'bar']);
        $this->given_IsVisible('foo');
        $this->given_IsVisible('bar');

        $this->given_HasTheParameters('foo', ['one']);
        $this->given_MayBeExecutedWith('foo', ['one' => 'uno']);

        $this->given_HasTheValue('one', 'uno');
        $this->assert->equals($this->executor->execute('foo'), new NoResult());

        $this->given_HasTheValue('one', 'un');
        $this->assert->equals($this->executor->execute('foo'), new NotPermittedResult());

        $this->assert->equals($this->executor->execute('bar'), new NotPermittedResult());
    }

    #############################################################

    /** @var ActionRegistry */
    private $registry;

    /** @var AccessControl */
    private $access;

    /** @var Action[] */
    private $actions = [];

    /** @var ParameterReader */
    private $reader;

    /** @var Executor */
    private $executor;

    /** @var Parameter[] */
    private $parameters;

    function before() {
        $this->access = Mockster::of(AccessControl::class);
        $this->registry = new ActionRegistry();
        $this->registry->restrictAccess(Mockster::mock($this->access));

        $this->reader = Mockster::of(ParameterReader::class);
        $fields = new FieldRegistry();
        $this->executor = new Executor(
            $this->registry,
            $fields,
            Mockster::mock(RendererRegistry::class),
            Mockster::mock($this->reader));
        $this->executor->restrictAccess(Mockster::mock($this->access));

        $fields->add(new StringField());
    }

    private function givenTheInvisibleActions(array $ids) {
        foreach ($ids as $id) {
            $this->actions[$id] = Mockster::of(Action::class);
            $this->registry->add($id, Mockster::mock($this->actions[$id]));
        }
    }

    private function given_IsVisible($actionId) {
        Mockster::stub($this->access->isVisible($actionId))->will()->return_(true);
    }

    private function given_MayBeExecutedWith($actionId, $parameters) {
        Mockster::stub($this->access->isExecutionPermitted($actionId, $parameters))->will()->return_(true);
    }

    private function given_HasTheParameters($actionId, array $parameterNames) {
        Mockster::stub($this->actions[$actionId]->parameters())
            ->will()->return_(array_map(function ($name) {
                return $this->parameter($name);
            }, $parameterNames));
    }

    private function given_HasTheValue($parameterName, $value) {
        $parameter = $this->parameter($parameterName);
        Mockster::stub($this->reader->has($parameter))->will()->return_(true);
        Mockster::stub($this->reader->read($parameter))->will()->return_($value);
    }

    private function parameter($name) {
        if (!$this->parameters[$name]) {
            $this->parameters[$name] = new Parameter($name, new StringType());
        }
        return $this->parameters[$name];
    }
}