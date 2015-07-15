<?php
namespace spec\rtens\domin\web;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\execution\RedirectResult;
use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\HeadElements;
use rtens\domin\web\menu\Menu;
use rtens\domin\web\root\IndexResource;
use rtens\domin\web\WebField;
use rtens\mockster\arguments\Argument as Arg;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\collections\Map;

/**
 * @property \spec\rtens\domin\fixtures\ActionFixture action <-
 * @property \spec\rtens\domin\fixtures\WebFixture web <-
 */
class ExecuteActionSpec extends StaticTestSuite {

    function unregisteredAction() {
        $this->whenIExecute('foo');
        $this->thenItShouldDisplayTheError('Action [foo] is not registered.');
    }

    function showError() {
        $this->action->givenTheAction_FailingWith('foo', 'Kaboom');
        $this->whenIExecute('foo');
        $this->thenItShouldDisplayTheError('Kaboom');
    }

    function showSuccessMessageIfNothingReturned() {
        $this->action->givenTheAction_Returning('foo', null);
        $this->whenIExecute('foo');
        $this->thenThereShouldBeASuccessMessageFor('Foo');
    }

    function showRenderedResult() {
        $this->action->givenTheAction_Returning('foo', 'bar');
        $this->givenAllValuesAreRenderedWith(function ($s) {
            return $s . '!';
        });
        $this->whenIExecute('foo');
        $this->thenItShouldShow('bar!');
    }

    function noMatchingField() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');

        $this->whenIExecute('foo');
        $this->thenItShouldDisplayTheError('No field found to handle [one:type of one]');
        $this->thenThereShouldBe_Fields(0);
        $this->thenThereShouldBe_HeadElements(3);
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

    function collectHeadElementsFromFields() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'bar');
        $this->action->given_HasTheParameter('foo', 'bas');

        $this->givenAWebFieldRequiringTheHeadElements(function (Parameter $parameter) {
            return [
                new Element('one'),
                new Element($parameter->getName()),
            ];
        });

        $this->whenIExecute('foo');

        $this->thenThereShouldBe_HeadElements(6);
        $this->thenHeadElement_ShouldBe(1, HeadElements::jquery());
        $this->thenHeadElement_ShouldBe(2, HeadElements::bootstrap());
        $this->thenHeadElement_ShouldBe(3, HeadElements::bootstrapJs());
        $this->thenHeadElement_ShouldBe(4, '<one></one>');
        $this->thenHeadElement_ShouldBe(5, '<bar></bar>');
        $this->thenHeadElement_ShouldBe(6, '<bas></bas>');
    }

    function fillParametersByAction() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'bar');
        $this->action->given_HasTheParameter('foo', 'bas');

        $this->givenAWebFieldRenderingWith(function (Parameter $p, $value) {
            return $p->getName() . ':' . $value;
        });

        $this->action->given_FillsParametersWith('foo', function ($params) {
            $params['bas'] = $params['bar'] . '!';
            return $params;
        });

        $this->whenIExecute_With('foo', ['bar' => 'hey']);

        $this->thenField_ShouldBeRenderedAs(1, 'bar:hey(inflated)');
        $this->thenField_ShouldBeRenderedAs(2, 'bas:hey(inflated)!');
    }

    function redirectResult() {
        $this->action->givenTheAction_Returning('foo', new RedirectResult('bar', ['one' => 'two']));
        $this->whenIExecute('foo');
        $this->thenThereShouldBeASuccessMessageFor('Foo');
        $this->thenIShouldBeRedirectedTo('http://example.com/base/bar?one=two');
    }

    /** @var RendererRegistry */
    private $renderers;

    /** @var FieldRegistry */
    public $fields;

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
            return $s . '(inflated)';
        });
        Mockster::stub($field->render(Arg::any(), Arg::any()))->will()->forwardTo($callback);
    }

    private function givenAWebFieldRequiringTheHeadElements($elements) {
        $field = Mockster::of(WebField::class);
        $this->fields->add(Mockster::mock($field));

        Mockster::stub($field->handles(Arg::any()))->will()->return_(true);
        Mockster::stub($field->inflate(Arg::any()))->will()->forwardTo(function ($s) {
            return $s . '(inflated)';
        });
        Mockster::stub($field->headElements(Arg::any()))->will()->forwardTo($elements);
    }

    private function whenIExecute($id) {
        $this->whenIExecute_With($id, []);
    }

    private function whenIExecute_With($id, $parameters) {
        $this->web->factory->setSingleton($this->action->registry);
        $this->web->factory->setSingleton($this->fields);
        $this->web->factory->setSingleton($this->renderers);
        $this->web->factory->setSingleton(new Menu($this->action->registry));

        $this->web->request = $this->web->request->withArguments(new Map($parameters));
        $this->web->whenIGet_From($id, IndexResource::class);
    }

    private function thenItShouldDisplayTheError($message) {
        $this->assert($this->web->model['error'], $message);
    }

    private function thenThereShouldBeASuccessMessageFor($actionId) {
        $this->assert($this->web->model['success']);
        $this->assert($this->web->model['action'], $actionId);
    }

    private function thenItShouldShow($value) {
        $this->assert($this->web->model['output'], $value);
    }

    private function thenThereShouldBe_Fields($count) {
        $this->assert->size($this->web->model['fields'], $count);
    }

    private function thenField_ShouldBe($pos, $name) {
        $this->assert($this->web->model['fields'][$pos - 1]['name'], $name);
    }

    private function thenField_ShouldBeRenderedAs($pos, $rendered) {
        $this->assert($this->web->model['fields'][$pos - 1]['control'], $rendered);
    }

    private function thenField_ShouldBeRequired($pos) {
        $this->assert($this->web->model['fields'][$pos - 1]['required']);
    }

    private function thenField_ShouldNotBeRequired($pos) {
        $this->assert->not($this->web->model['fields'][$pos - 1]['required']);
    }

    private function thenThereShouldBe_HeadElements($int) {
        $this->assert->size($this->web->model['headElements'], $int);
    }

    private function thenHeadElement_ShouldBe($pos, $string) {
        $this->assert($this->web->model['headElements'][$pos - 1], $string);
    }

    private function thenIShouldBeRedirectedTo($url) {
        $this->assert($this->web->model['redirect'], $url);
    }
}