<?php
namespace spec\rtens\domin\web;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\Parameter;
use rtens\domin\web\root\ExecuteResource;
use rtens\domin\web\WebField;
use rtens\mockster\arguments\Argument as Arg;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\collections\Map;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\Url;
use watoki\deli\Path;

/**
 * @property \spec\rtens\domin\fixtures\ActionFixture action <-
 */
class ExecuteActionSpec extends StaticTestSuite {

    function showError() {
        $this->action->givenTheAction_FailingWith('foo', 'Kaboom');
        $this->whenIExecute('foo');
        $this->thenItShouldDisplayTheError('Kaboom');
    }

    function showSuccessMessageIfNothingReturned() {
        $this->action->givenTheAction_Returning('foo', null);
        $this->whenIExecute('foo');
        $this->thenTheSuccessMessageFor_ShouldBe('foo');
    }

    function showRenderedResult() {
        $this->action->givenTheAction_Returning('foo', 'bar');
        $this->givenAllValuesAreRenderedWith(function ($s) {
            return $s . '!';
        });
        $this->whenIExecute('foo');
        $this->thenItShouldShow('bar!');
    }

    function showFields() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->action->given_HasTheParameter('foo', 'two');

        $this->givenAWebFieldRenderingWith(function (Parameter $p) {
            return $p->getName() . ':';
        });

        $this->whenIExecute('foo');

        $this->thenThereShouldBe_Fields(2);
        $this->thenField_ShouldBe(1, "one");
        $this->thenField_ShouldBe(2, "two");
        $this->thenField_ShouldBeRenderedAs(1, "one:");
        $this->thenField_ShouldBeRenderedAs(2, "two:");
    }

    function fillFieldsWithParameters() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->action->given_HasTheParameter('foo', 'two');

        $this->givenAWebFieldRenderingWith(function (Parameter $p, $value) {
            return $p->getName() . ':' . $value;
        });

        $this->whenIExecute_With('foo', ['two' => 'dos']);
        $this->thenThereShouldBe_Fields(2);
        $this->thenField_ShouldBeRenderedAs(1, "one:");
        $this->thenField_ShouldBeRenderedAs(2, "two:dos(inflated)");
    }

    function markRequiredFields() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->action->given_HasTheRequiredParameter('foo', 'two');

        $this->givenAWebFieldRenderingWith(function (Parameter $p, $value) {
            return $p->getName() . ($p->isRequired() ? '*' : '') . ':' . $value;
        });

        $this->whenIExecute_With('foo', ['three' => 'tres']);
        $this->thenThereShouldBe_Fields(2);
        $this->thenField_ShouldNotBeRequired(1);
        $this->thenField_ShouldBeRequired(2);
        $this->thenField_ShouldBeRenderedAs(1, "one:");
        $this->thenField_ShouldBeRenderedAs(2, "two*:");
    }

    /** @var RendererRegistry */
    private $renderers;

    /** @var FieldRegistry */
    public $fields;

    private $model;

    protected function before() {
        $this->renderers = new RendererRegistry();
        $this->fields = new FieldRegistry();
    }

    private function givenAllValuesAreRenderedWith($callback) {
        $renderer = Mockster::of(Renderer::class);
        $this->renderers->add(Mockster::mock($renderer));

        Mockster::stub($renderer->handles(Arg::any()))->will()->return_(true);
        Mockster::stub($renderer->render(Arg::any()))->will()->forwardTo($callback);
    }

    private function givenAWebFieldRenderingWith($callback) {
        $field = Mockster::of(WebField::class);
        $this->fields->add(Mockster::mock($field));

        Mockster::stub($field->handles(Arg::any()))->will()->return_(true);
        Mockster::stub($field->inflate(Arg::any()))->will()->forwardTo(function ($s) {
            return $s ? $s . '(inflated)' : null;
        });
        Mockster::stub($field->render(Arg::any(), Arg::any(), Arg::any()))->will()->forwardTo($callback);
    }

    private function whenIExecute($id) {
        $this->whenIExecute_With($id, []);
    }

    private function whenIExecute_With($id, $parameters) {
        $resource = new ExecuteResource($this->action->registry, $this->fields, $this->renderers);
        $this->model = $resource->doGet($id, new WebRequest(Url::fromString('http://domin.dev/base'), new Path(), null, new Map($parameters)));
    }

    private function thenItShouldDisplayTheError($message) {
        $this->assert($this->model['error'], $message);
    }

    private function thenTheSuccessMessageFor_ShouldBe($actionId) {
        $this->assert($this->model['success']['action'], $actionId);
    }

    private function thenItShouldShow($value) {
        $this->assert($this->model['output'], $value);
    }

    private function thenThereShouldBe_Fields($count) {
        $this->assert->size($this->model['fields'], $count);
    }

    private function thenField_ShouldBe($pos, $name) {
        $this->assert($this->model['fields'][$pos - 1]['name'], $name);
    }

    private function thenField_ShouldBeRenderedAs($pos, $rendered) {
        $this->assert($this->model['fields'][$pos - 1]['control'], $rendered);
    }

    private function thenField_ShouldBeRequired($pos) {
        $this->assert($this->model['fields'][$pos - 1]['required']);
    }

    private function thenField_ShouldNotBeRequired($pos) {
        $this->assert->not($this->model['fields'][$pos - 1]['required']);
    }
}