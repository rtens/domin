<?php
namespace spec\rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\delivery\web\BreadCrumb;
use rtens\domin\delivery\web\BreadCrumbsTrail;
use rtens\domin\delivery\web\fields\ActionField;
use rtens\domin\delivery\web\fields\StringField;
use rtens\domin\delivery\web\home\ListActions;
use rtens\domin\delivery\web\renderers\PrimitiveRenderer;
use rtens\domin\delivery\web\resources\ExecutionResource;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\Parameter;
use rtens\mockster\arguments\Argument as Arg;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use spec\rtens\domin\fixtures\FakeParameterReader;
use watoki\factory\Factory;
use watoki\reflect\type\StringType;

class SprinkleBreadcrumbsSpec extends StaticTestSuite {

    /** @var BreadCrumbsTrail */
    private $crumbs;

    /** @var Action */
    private $action;

    /** @var WebApplication */
    private $app;

    private $response;

    /** @var BreadCrumb[] */
    private $crumbSource = [];

    protected function before() {
        $factory = new Factory();
        $this->app = $factory->getInstance(WebApplication::class);
        $this->app->prepare();

        $this->app->fields->add(new ActionField($this->app->fields, $this->app->actions));
        $this->app->renderers->add(new PrimitiveRenderer());
        $this->app->fields->add(new StringField());
    }

    function addCrumbIfActionRendered() {
        $this->givenANonModifyingAction('foo');

        $this->whenIExecute_With('foo', ['one' => 'uno']);
        $this->thenTheCrumbs_ShouldBeSaved([
            new BreadCrumb('My Foo', 'foo?one=uno')
        ]);
    }

    function noCrumbIfNoResult() {
        $this->givenANonModifyingAction('foo');
        Mockster::stub($this->action->execute(Arg::any()))->will()->return_(null);

        $this->whenIExecute('foo');
        $this->thenNoCrumbsShouldBeSaved();
    }

    function noCrumbIfActionIsModifying() {
        $this->givenANonModifyingAction('foo');
        Mockster::stub($this->action->isModifying())->will()->return_(true);

        $this->whenIExecute_With('foo', ['one' => 'uno']);
        $this->thenNoCrumbsShouldBeSaved();
    }

    function emptyCrumbs() {
        $this->givenANonModifyingAction('foo');

        $this->whenIExecute('foo');
        $this->assert->contains($this->response, '
        <ol class="breadcrumb">
                <li><a href="foo">My Foo</a></li>
            </ol>');
    }

    function displayCrumbs() {
        $this->givenTheSavedCrumbs([
            new BreadCrumb('My Foo', 'foo'),
            new BreadCrumb('My Bar', 'bar'),
        ]);

        $this->app->actions->add('foo', Mockster::mock(Action::class));

        $this->whenIExecute('foo');
        $this->thenTheBreadCrumbsShouldBe([
            new BreadCrumb('My Foo', 'foo'),
            new BreadCrumb('My Bar', 'bar')
        ]);
    }

    function jumpBack() {
        $this->givenTheSavedCrumbs([
            new BreadCrumb('My Foo', 'bar'),
            new BreadCrumb('My Foo', 'foo?one=uno'),
            new BreadCrumb('My Foo', 'foo'),
            new BreadCrumb('My Foo', 'foo?one=not'),
        ]);

        $this->givenANonModifyingAction('foo');

        $this->whenIExecute_With('foo', ['one' => 'uno']);

        $this->thenTheCrumbs_ShouldBeSaved([
            new BreadCrumb('My Foo', 'bar'),
            new BreadCrumb('My Foo', 'foo?one=uno')
        ]);
    }

    function redirectToLastCrumbIfNoResult() {
        $this->app->fields->add(new ActionField($this->app->fields, $this->app->actions));

        $this->givenTheSavedCrumbs([
            new BreadCrumb('My Bar', 'path/to/bar'),
            new BreadCrumb('My Foo', 'path/to/foo'),
        ]);

        $action = Mockster::of(Action::class);
        Mockster::stub($action->execute(Arg::any()))->will()->return_(null);
        $this->app->actions->add('foo', Mockster::mock($action));

        $this->whenIExecute('foo');
        $this->assert->contains($this->response,
            '<meta http-equiv="refresh" content="2; URL=path/to/foo">');
    }

    function doNotRedirectIfNoCrumbsSprinkled() {
        $this->givenThereAreNoSavedCrumbs();

        $action = Mockster::of(Action::class);
        Mockster::stub($action->execute(Arg::any()))->will()->return_(null);
        $this->app->actions->add('foo', Mockster::mock($action));

        $this->whenIExecute('foo');
        $this->assert->not()->contains($this->response,
            '<meta http-equiv="refresh"');
    }

    function overviewResetsCrumbs() {
        $this->whenIListAllActions();
        $this->thenTheCrumbs_ShouldBeSaved([
            new BreadCrumb('Actions', 'index')
        ]);
    }

    private function whenIExecute($action) {
        $this->whenIExecute_With($action, []);
    }

    private function whenIExecute_With($action, $parameters) {
        $reader = new FakeParameterReader($parameters);
        $this->crumbs = new BreadCrumbsTrail($reader, $this->crumbSource);

        $resource = new ExecutionResource($this->app, $reader, $this->crumbs);

        $this->response = $resource->handleGet($action);
    }

    private function thenTheCrumbs_ShouldBeSaved($crumbs) {
        $this->assert($this->crumbs->getCrumbs(), $crumbs);
    }

    private function thenNoCrumbsShouldBeSaved() {
        $this->thenTheCrumbs_ShouldBeSaved([]);
    }

    private function givenTheSavedCrumbs($crumbs) {
        $this->crumbSource = $crumbs;
    }

    private function givenThereAreNoSavedCrumbs() {
        $this->givenTheSavedCrumbs([]);
    }

    private function whenIListAllActions() {
        $this->app->actions->add('index', new ListActions($this->app->actions, $this->app->groups, $this->app->access, $this->app->parser));
        $this->whenIExecute_With('index', []);
    }

    /**
     * @param BreadCrumb[] $crumbs
     */
    private function thenTheBreadCrumbsShouldBe($crumbs) {
        foreach ($crumbs as $crumb) {
            $this->assert->contains($this->response,
                sprintf('<li><a href="%s">%s</a></li>', $crumb->getTarget(), $crumb->getCaption()));
        }
    }

    private function givenANonModifyingAction($actionId) {
        $this->action = Mockster::of(Action::class);
        $this->app->actions->add($actionId, Mockster::mock($this->action));

        Mockster::stub($this->action->execute(Arg::any()))->will()->return_('Value of ' . $actionId);
        Mockster::stub($this->action->isModifying())->will()->return_(false);
        Mockster::stub($this->action->caption())->will()->return_('My Foo');
        Mockster::stub($this->action->parameters())->will()->return_([
            new Parameter('one', new StringType())
        ]);
    }
}