<?php
namespace spec\rtens\domin\delivery\web;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\BreadCrumbsTrail;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\menu\Menu;
use rtens\domin\delivery\web\resources\ExecutionResource;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\delivery\web\WebField;
use rtens\domin\execution\RedirectResult;
use rtens\domin\Parameter;
use rtens\mockster\arguments\Argument;
use rtens\mockster\arguments\Argument as Arg;
use rtens\mockster\Mockster;
use rtens\scrut\fixtures\ExceptionFixture;
use rtens\scrut\tests\statics\StaticTestSuite;
use spec\rtens\domin\fixtures\FakeParameterReader;
use watoki\factory\Factory;

/**
 * @property \spec\rtens\domin\fixtures\ActionFixture action <-
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

        $this->givenAWebFieldRenderingWith(function () {
            return 'rendered';
        });

        $this->whenIExecute('foo');
        $this->thenThereShouldBeASuccessMessageFor('Foo');
        $this->thenIShouldBeRedirectedTo('bar?one=two');
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

        $this->thenThereShouldBeAHeadElement(HeadElements::jquery());
        $this->thenThereShouldBeAHeadElement(HeadElements::jqueryUi());
        $this->thenThereShouldBeAHeadElement(HeadElements::bootstrap());
        $this->thenThereShouldBeAHeadElement(HeadElements::bootstrapJs());
        $this->thenThereShouldBeAHeadElement('<one></one>');
        $this->thenThereShouldBeAHeadElement('<foo></foo>');
    }

    function passParametersToActionField() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->action->given_HasTheParameter('foo', 'two');

        $this->givenAWebFieldRenderingWith(function (Parameter $action, $parameters) {
            $inflated = $parameters['inflated'];
            array_walk($inflated, function (&$v, $k) {
                $v = "$k:$v";
            });
            return $action->getName() . ' - ' . implode(',', $inflated);
        });

        $this->whenIExecute_With('foo', ['two' => 'dos', 'three' => 'not a parameter']);
        $this->thenTheActionShouldBeRenderedAs('foo - two:two_dos(inflated)');
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

    /** @var string */
    private $response;

    /** @var Factory */
    private $factory;

    /** @var RendererRegistry */
    private $renderers;

    /** @var FieldRegistry */
    public $fields;

    protected function before() {
        $this->factory = new Factory();
        $this->renderers = new RendererRegistry();
        $this->fields = new FieldRegistry();

        $this->factory->setSingleton($this->action->registry);
        $this->factory->setSingleton($this->fields);
        $this->factory->setSingleton($this->renderers);
        $this->factory->setSingleton(new Menu($this->action->registry));
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
        /** @var WebApplication $app */
        $app = $this->factory->getInstance(WebApplication::class);
        $app->prepare();

        $reader = new FakeParameterReader($parameters);
        $execution = new ExecutionResource($app, $reader, new BreadCrumbsTrail($reader, []));
        $this->response = $execution->handlePost($id);
    }

    private function thenItShouldDisplayTheError($message) {
        $this->assert->contains($this->response,
            sprintf('<div class="alert alert-danger">%s</div>', $message));
    }

    private function thenThereShouldBeASuccessMessageFor($actionId) {
        $this->assert->contains($this->response,
            sprintf('<div class="alert alert-success">Action "%s" was successfully executed</div>', $actionId));
    }

    private function thenItShouldShow($value) {
        $this->assert->contains($this->response, $value);
    }

    private function thenThereShouldBeAHeadElement($string) {
        $this->assert->contains($this->response, (string)$string);
    }

    private function thenIShouldBeRedirectedTo($url) {
        $this->assert->contains($this->response,
            sprintf('<div class="alert alert-info">You are redirected. Please wait or <a href="%s">click here</a></div>', $url));
        $this->assert->contains($this->response,
            sprintf('<meta http-equiv="refresh" content="2; URL=%s">', $url));
    }

    private function thenTheActionShouldBeRenderedAs($string) {
        $this->assert->contains($this->response, $string);
    }

    private function thenItShouldFailWith($message) {
        $this->try->thenTheException_ShouldBeThrown($message);
    }

    private function thenTheOutputShouldBe($output) {
        $this->assert->contains($this->response, $output);
    }
}