<?php
namespace spec\rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\delivery\web\BreadCrumbs;
use rtens\domin\delivery\web\fields\ActionField;
use rtens\domin\delivery\web\fields\StringField;
use rtens\domin\delivery\web\renderers\PrimitiveRenderer;
use rtens\domin\delivery\web\root\ExecuteResource;
use rtens\domin\delivery\web\root\IndexResource;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\Parameter;
use rtens\mockster\arguments\Argument as Arg;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\collections\Map;
use watoki\curir\cookie\Cookie;
use watoki\curir\cookie\CookieStore;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\Url;
use watoki\deli\Path;
use watoki\factory\Factory;
use watoki\reflect\type\StringType;

class SprinkleBreadcrumbsSpec extends StaticTestSuite {

    /** @var Action */
    private $action;

    /** @var CookieStore */
    private $cookies;

    /** @var ExecuteResource */
    private $resource;

    /** @var WebApplication */
    private $app;

    private $model;

    protected function before() {
        $this->cookies = Mockster::of(CookieStore::class);

        $factory = new Factory();
        $this->app = $factory->getInstance(WebApplication::class);

        $this->resource = new ExecuteResource(new Factory(), $this->app, Mockster::mock($this->cookies));

        $this->app->fields->add(new ActionField($this->app->fields, $this->app->actions));
        $this->app->renderers->add(new PrimitiveRenderer());
        $this->app->fields->add(new StringField());
    }

    function addCrumbIfActionRendered() {
        $this->givenANonModifyingAction('foo');

        $this->whenIExecute_With('foo', ['one' => 'uno']);
        $this->thenTheCrumbs_ShouldBeSaved([
            ['target' => 'http://example.com/base/foo?one=uno', 'caption' => 'My Foo']
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
        $this->assert->size($this->model['breadcrumbs'], 0);
    }

    function displayCrumbs() {
        $this->givenTheSavedCrumbs([
            ['target' => 'foo', 'caption' => 'My Foo'],
            ['target' => 'bar', 'caption' => 'My Bar'],
        ]);

        $this->app->actions->add('foo', Mockster::mock(Action::class));

        $this->whenIExecute('foo');
        $this->thenTheBreadCrumbsShouldBe([
            ['target' => 'foo', 'caption' => 'My Foo'],
            ['target' => 'bar', 'caption' => 'My Bar']
        ]);
    }

    function jumpBack() {
        $this->givenTheSavedCrumbs([
            ['target' => 'http://example.com/base/bar', 'caption' => 'My Foo'],
            ['target' => 'http://example.com/base/foo?one=uno', 'caption' => 'My Foo'],
            ['target' => 'http://example.com/base/foo', 'caption' => 'My Foo'],
            ['target' => 'http://example.com/base/foo?one=not', 'caption' => 'My Foo'],
        ]);

        $this->givenANonModifyingAction('foo');

        $this->whenIExecute_With('foo', ['one' => 'uno']);

        $this->thenTheCrumbs_ShouldBeSaved([
            ['target' => 'http://example.com/base/bar', 'caption' => 'My Foo'],
            ['target' => 'http://example.com/base/foo?one=uno', 'caption' => 'My Foo']
        ]);
    }

    function redirectToLastCrumbIfNoResult() {
        $this->app->fields->add(new ActionField($this->app->fields, $this->app->actions));

        $this->givenTheSavedCrumbs([
            ['target' => 'path/to/bar', 'caption' => 'My Bar'],
            ['target' => 'path/to/foo', 'caption' => 'My Foo'],
        ]);

        $action = Mockster::of(Action::class);
        Mockster::stub($action->execute(Arg::any()))->will()->return_(null);
        $this->app->actions->add('foo', Mockster::mock($action));

        $this->whenIExecute('foo');
        $this->assert($this->model['result']['redirect'], 'path/to/foo');
    }

    function doNotRedirectIfNoCrumbsSprinkled() {
        $this->givenThereAreNoSavedCrumbs();

        $action = Mockster::of(Action::class);
        Mockster::stub($action->execute(Arg::any()))->will()->return_(null);
        $this->app->actions->add('foo', Mockster::mock($action));

        $this->whenIExecute('foo');
        $this->assert->isNull($this->model['result']['redirect']);
    }

    function overviewResetsCrumbs() {
        $this->whenIListAllActions();
        $this->thenTheCrumbs_ShouldBeSaved([]);
    }

    private function whenIExecute($action) {
        $this->whenIExecute_With($action, []);
    }

    private function whenIExecute_With($action, $parameters) {
        $this->model = $this->resource->doGet($action, $this->makeRequest($parameters));
    }

    private function makeRequest($parameters = []) {
        return new WebRequest(Url::fromString('http://example.com/base'), new Path(), null, new Map($parameters));
    }

    private function thenTheCrumbs_ShouldBeSaved($payload) {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $stub = Mockster::stub($this->cookies->create(Arg::any(), Arg::any()));
        $this->assert($stub->has()->beenCalled());
        $this->assert($stub->has()->inCall(0)->argument(0)->payload, $payload);
    }

    private function thenNoCrumbsShouldBeSaved() {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assert->not(Mockster::stub($this->cookies->create(Arg::any(), BreadCrumbs::COOKIE_KEY))
            ->has()->beenCalled());
    }

    private function givenTheSavedCrumbs($payload) {
        Mockster::stub($this->cookies->hasKey(BreadCrumbs::COOKIE_KEY))->will()->return_(true);
        Mockster::stub($this->cookies->read(BreadCrumbs::COOKIE_KEY))
            ->will()->return_(new Cookie($payload));
    }

    private function givenThereAreNoSavedCrumbs() {
        Mockster::stub($this->cookies->hasKey(BreadCrumbs::COOKIE_KEY))->will()->return_(false);
    }

    private function whenIListAllActions() {
        $resource = new IndexResource(
            new Factory(),
            $this->app,
            Mockster::mock($this->cookies));

        $resource->doGet($this->makeRequest());
    }

    private function thenTheBreadCrumbsShouldBe($crumbs) {
        $this->assert($this->model['breadcrumbs'], $crumbs);
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