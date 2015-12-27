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
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\fixtures\ExceptionFixture;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\collections\Map;
use watoki\curir\delivery\WebRequest;

/**
 * @property \spec\rtens\domin\fixtures\ActionFixture action <-
 * @property \spec\rtens\domin\fixtures\WebFixture web <-
 * @property ExceptionFixture try <-
 */
class ExecuteActionSpec extends StaticTestSuite {

    function unregisteredAction() {
        $this->whenITryToExecute('foo');
        $this->thenItShouldFailWith('Action [foo] is not registered.');
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

        $this->whenITryToExecute('foo');
        $this->thenItShouldFailWith('No field found to handle [one:type of one]');
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
            $inflated = $parameters['inflated'];
            array_walk($inflated, function (&$v, $k) {
                $v ="$k:$v";
            });
            return $action->getName() . ' - ' . implode(',', $inflated);
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

        $this->whenITryToExecute('foo');
        $this->thenItShouldFailWith('Action [foo] is not registered.');
    }

    /**
     *
     */
    function denyExecution() {
        $this->action->givenTheAction('foo');

        $access = Mockster::of(AccessControl::class);
        Mockster::stub($access->isVisible('foo'))->will()->return_(true);

        WebApplication::init(function (WebApplication $app) use ($access) {
            $app->restrictAccess(WebAccessControl::factory(Mockster::mock($access)));
        }, $this->web->factory);

        $this->whenIExecute('foo');
        $this->thenItShouldDisplayTheError('You are not permitted to execute this action.');
        $this->thenIShouldNotBeRedirected();
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
        $this->thenIShouldBeRedirectedTo('http://example.com/base/acquire_this');
    }

    function renderResult() {
        $this->action->givenTheAction_Returning('foo', 'this is foo');
        $this->givenTheRenderer(function ($in) {
            return $in . ' rendered';
        });

        $this->whenIExecute('foo');
        $this->thenTheOutputShouldBe('this is foo rendered');
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
        $this->thenTheOutputShouldBe('this is foo with foo');

        $this->whenIExecute('bar');
        $this->thenTheOutputShouldBe('this is bar with bar');
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

    private function givenTheRenderer($callback) {
        $this->givenARendererFor_RenderingWith(Argument::any(), $callback);
    }

    private function givenARendererFor_RenderingWith($value, $callback) {
        $renderer = Mockster::of(Renderer::class);
        $this->renderers->add(Mockster::mock($renderer));

        Mockster::stub($renderer->handles($value))->will()->return_(true);
        Mockster::stub($renderer->render(Argument::any()))->will()->forwardTo($callback);
    }

    private function whenITryToExecute($id) {
        $this->try->tryTo(function () use ($id) {
            $this->whenIExecute($id);
        });
    }

    private function whenIExecute($id) {
        $this->whenIExecute_With($id, []);
    }

    private function whenIExecute_With($id, $parameters) {
        $this->web->request = $this->web->request->withArguments(new Map($parameters));
        $this->web->whenIGet_From($id, IndexResource::class);
    }

    private function thenItShouldDisplayTheError($message) {
        $this->assert($this->web->model['result']['error'], $message);
    }

    private function thenThereShouldBeASuccessMessageFor($actionId) {
        $this->assert($this->web->model['result']['success']);
        $this->assert($this->web->model['caption'], $actionId);
    }

    private function thenItShouldShow($value) {
        $this->assert($this->web->model['result']['output'], $value);
    }

    private function thenThereShouldBe_HeadElements($int) {
        $this->assert->size($this->web->model['headElements'], $int);
    }

    private function thenHeadElement_ShouldBe($pos, $string) {
        $this->assert($this->web->model['headElements'][$pos - 1], $string);
    }

    private function thenIShouldNotBeRedirected() {
        $this->thenIShouldBeRedirectedTo(null);
    }

    private function thenIShouldBeRedirectedTo($url) {
        $this->assert($this->web->model['result']['redirect'], $url);
    }

    private function thenTheActionShouldBeRenderedAs($string) {
        $this->assert($this->web->model['action'], $string);
    }

    private function thenItShouldFailWith($message) {
        $this->try->thenTheException_ShouldBeThrown($message);
    }

    private function thenTheOutputShouldBe($output) {
        $this->assert($this->web->model['result']['output'], $output);
    }
}