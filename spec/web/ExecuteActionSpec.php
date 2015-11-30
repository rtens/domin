<?php
namespace spec\rtens\domin\delivery\web;

use rtens\domin\AccessControl;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\menu\Menu;
use rtens\domin\delivery\web\root\IndexResource;
use rtens\domin\delivery\web\WebAccessControl;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\delivery\web\WebField;
use rtens\domin\execution\RedirectResult;
use rtens\domin\Parameter;
use rtens\mockster\arguments\Argument as Arg;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\collections\Map;
use watoki\curir\delivery\WebRequest;

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
        $this->thenThereShouldBeNoAction();
        $this->thenThereShouldBe_HeadElements(4);
    }

    function redirectResult() {
        $this->action->givenTheAction_Returning('foo', new RedirectResult('bar', ['one' => 'two']));
        $this->action->given_HasTheParameter('foo', 'one');

        $this->givenAWebFieldRenderingWith(function (Parameter $p, $value) {
            return $p->getName() . ($p->isRequired() ? '*' : '') . ':' . $value;
        });

        $this->whenIExecute('foo');
        $this->thenThereShouldBeASuccessMessageFor('Foo');
        $this->thenIShouldBeRedirectedTo('http://example.com/base/bar?one=two');
        $this->thenThereShouldBeNoAction();
    }

    function collectHeadElements() {
        $this->action->givenTheAction('foo');

        $this->givenAWebFieldRequiringTheHeadElements(function (Parameter $parameter) {
            return [
                new Element('one'),
                new Element($parameter->getName()),
            ];
        });

        $this->whenIExecute('foo');

        $this->thenThereShouldBe_HeadElements(6);
        $this->thenHeadElement_ShouldBe(1, HeadElements::jquery());
        $this->thenHeadElement_ShouldBe(2, HeadElements::jqueryUi());
        $this->thenHeadElement_ShouldBe(3, HeadElements::bootstrap());
        $this->thenHeadElement_ShouldBe(4, HeadElements::bootstrapJs());
        $this->thenHeadElement_ShouldBe(5, '<one></one>');
        $this->thenHeadElement_ShouldBe(6, '<foo></foo>');
    }

    function passParametersToActionField() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->action->given_HasTheParameter('foo', 'two');

        $this->givenAWebFieldRenderingWith(function (Parameter $action, $parameters) {
            array_walk($parameters, function (&$v, $k) {
                $v ="$k:$v";
            });
            return $action->getName() . ' - ' . implode(',', $parameters);
        });

        $this->whenIExecute_With('foo', ['two' => 'dos', 'three' => 'not a parameter']);
        $this->thenTheActionShouldBeRenderedAs('foo - two:two_dos(inflated)');
    }

    function denyInvisibleAction() {
        $this->action->givenTheAction('foo');

        $access = Mockster::of(AccessControl::class);

        WebApplication::init(function (WebApplication $app) use ($access) {
            $app->restrictAccess(WebAccessControl::factory(Mockster::mock($access)));
        }, $this->web->factory);

        $this->whenIExecute('foo');
        $this->assert($this->web->model['error'], 'Action [foo] is not registered.');
    }

    function denyExecution() {
        $this->action->givenTheAction('foo');

        $access = Mockster::of(AccessControl::class);
        Mockster::stub($access->isVisible('foo'))->will()->return_(true);

        WebApplication::init(function (WebApplication $app) use ($access) {
            $app->restrictAccess(WebAccessControl::factory(Mockster::mock($access)));
        }, $this->web->factory);

        $this->whenIExecute('foo');
        $this->assert($this->web->model['error'], 'You are not permitted to execute this action.');
        $this->assert->isNull($this->web->model['redirect']);
    }

    function redirectToAcquirePermission() {
        $this->action->givenTheAction('foo');

        $access = Mockster::of(AccessControl::class);
        Mockster::stub($access->isVisible('foo'))->will()->return_(true);

        WebApplication::init(function (WebApplication $app) use ($access) {
            $app->restrictAccess(WebAccessControl::factory(Mockster::mock($access), function (WebRequest $request) {
                return $request->getContext()->appended('acquire_this');
            }));
        }, $this->web->factory);

        $this->whenIExecute('foo');
        $this->assert($this->web->model['redirect'], 'http://example.com/base/acquire_this');
    }

    ####################################################################################################

    /** @var RendererRegistry */
    private $renderers;

    /** @var FieldRegistry */
    public $fields;

    protected function before() {
        $this->renderers = new RendererRegistry();
        $this->fields = new FieldRegistry();

        $this->web->factory->setSingleton($this->action->registry);
        $this->web->factory->setSingleton($this->fields);
        $this->web->factory->setSingleton($this->renderers);
        $this->web->factory->setSingleton(new Menu($this->action->registry));
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
        Mockster::stub($field->inflate(Arg::any(), Arg::any()))->will()->forwardTo(function (Parameter $p, $s) {
            return $p->getName() . '_' . $s . '(inflated)';
        });
        Mockster::stub($field->render(Arg::any(), Arg::any()))->will()->forwardTo($callback);
    }

    private function givenAWebFieldRequiringTheHeadElements($elements) {
        $field = Mockster::of(WebField::class);
        $this->fields->add(Mockster::mock($field));

        Mockster::stub($field->handles(Arg::any()))->will()->return_(true);
        Mockster::stub($field->inflate(Arg::any(), Arg::any()))->will()->forwardTo(function ($s) {
            return $s . '(inflated)';
        });
        Mockster::stub($field->headElements(Arg::any()))->will()->forwardTo($elements);
    }

    private function whenIExecute($id) {
        $this->whenIExecute_With($id, []);
    }

    private function whenIExecute_With($id, $parameters) {
        $this->web->request = $this->web->request->withArguments(new Map($parameters));
        $this->web->whenIGet_From($id, IndexResource::class);
    }

    private function thenItShouldDisplayTheError($message) {
        $this->assert($this->web->model['error'], $message);
    }

    private function thenThereShouldBeASuccessMessageFor($actionId) {
        $this->assert($this->web->model['success']);
        $this->assert($this->web->model['caption'], $actionId);
    }

    private function thenItShouldShow($value) {
        $this->assert($this->web->model['output'], $value);
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

    private function thenThereShouldBeNoAction() {
        $this->thenTheActionShouldBeRenderedAs(null);
    }

    private function thenTheActionShouldBeRenderedAs($string) {
        $this->assert($this->web->model['action'], $string);
    }
}