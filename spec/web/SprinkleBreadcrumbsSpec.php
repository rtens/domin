<?php
namespace spec\rtens\domin\web;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\Parameter;
use rtens\domin\web\fields\PrimitiveField;
use rtens\domin\web\menu\Menu;
use rtens\domin\web\root\ExecuteResource;
use rtens\domin\web\root\IndexResource;
use rtens\domin\web\WebApplication;
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

    /** @var ActionRegistry */
    private $actions;

    /** @var CookieStore */
    private $cookies;

    /** @var ExecuteResource */
    private $resource;

    private $model;

    protected function before() {
        $this->actions = new ActionRegistry();
        $this->cookies = Mockster::of(CookieStore::class);
        $fields = Mockster::of(FieldRegistry::class);
        $this->resource = new ExecuteResource(new Factory(), $this->actions,
            Mockster::mock($fields), Mockster::mock(RendererRegistry::class),
            Mockster::mock(Menu::class), Mockster::mock($this->cookies));

        Mockster::stub($fields->getField(Arg::any()))->will()->return_(new PrimitiveField());
    }

    function addCrumbIfActionRendered() {
        $action = Mockster::of(Action::class);
        $this->actions->add('foo', Mockster::mock($action));

        Mockster::stub($action->execute(Arg::any()))->will()->return_('foo');
        Mockster::stub($action->caption())->will()->return_('My Foo');
        Mockster::stub($action->parameters())->will()->return_([
            new Parameter('one', new StringType())
        ]);

        $this->whenIExecute_With('foo', ['one' => 'uno']);
        $this->thenTheCrumbs_ShouldBeSaved([
            ['target' => 'http://example.com/base/foo?one=uno', 'caption' => 'My Foo']
        ]);
    }

    function noCrumbIfActionNotRendered() {
        $action = Mockster::of(Action::class);
        $this->actions->add('foo', Mockster::mock($action));

        Mockster::stub($action->execute(Arg::any()))->will()->return_(null);

        $this->whenIExecute('foo');
        $this->thenNoCrumbsShouldBeSaved();

    }

    function emptyCrumbs() {
        $this->actions->add('foo', Mockster::mock(Action::class));

        $this->whenIExecute('foo');
        $this->assert->size($this->model['breadcrumbs'], 0);
    }

    function displayCrumbs() {
        $this->givenTheSavedCrumbs([
            ['target' => 'foo', 'caption' => 'My Foo'],
            ['target' => 'bar', 'caption' => 'My Bar'],
        ]);

        $this->actions->add('foo', Mockster::mock(Action::class));

        $this->whenIExecute('foo');
        $this->assert($this->model['breadcrumbs'], [
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

        $action = Mockster::of(Action::class);
        Mockster::stub($action->caption())->will()->return_('My Foo');
        Mockster::stub($action->execute(Arg::any()))->will()->return_('bar');
        Mockster::stub($action->parameters())->will()->return_([
            new Parameter('one', new StringType())
        ]);
        $this->actions->add('foo', Mockster::mock($action));

        $this->whenIExecute_With('foo', ['one' => 'uno']);
        $this->assert($this->model['breadcrumbs'], [
            ['target' => 'http://example.com/base/bar', 'caption' => 'My Foo']
        ]);

        $this->thenTheCrumbs_ShouldBeSaved([
            ['target' => 'http://example.com/base/bar', 'caption' => 'My Foo'],
            ['target' => 'http://example.com/base/foo?one=uno', 'caption' => 'My Foo']
        ]);
    }

    function redirectToLastCrumbIfNoResult() {
        $this->givenTheSavedCrumbs([
            ['target' => 'path/to/bar', 'caption' => 'My Bar'],
            ['target' => 'path/to/foo', 'caption' => 'My Foo'],
        ]);

        $action = Mockster::of(Action::class);
        Mockster::stub($action->execute(Arg::any()))->will()->return_(null);
        $this->actions->add('foo', Mockster::mock($action));

        $this->whenIExecute('foo');
        $this->assert($this->model['redirect'], 'path/to/foo');
    }

    function doNotRedirectIfNoCrumbsSprinkled() {
        $this->givenThereAreNoSavedCrumbs();

        $action = Mockster::of(Action::class);
        Mockster::stub($action->execute(Arg::any()))->will()->return_(null);
        $this->actions->add('foo', Mockster::mock($action));

        $this->whenIExecute('foo');
        $this->assert->isNull($this->model['redirect']);
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
        $this->assert->not(Mockster::stub($this->cookies->create(Arg::any(), ExecuteResource::BREADCRUMB_COOKIE))
            ->has()->beenCalled());
    }

    private function givenTheSavedCrumbs($payload) {
        Mockster::stub($this->cookies->hasKey(ExecuteResource::BREADCRUMB_COOKIE))->will()->return_(true);
        Mockster::stub($this->cookies->read(ExecuteResource::BREADCRUMB_COOKIE))
            ->will()->return_(new Cookie($payload));
    }

    private function givenThereAreNoSavedCrumbs() {
        Mockster::stub($this->cookies->hasKey(ExecuteResource::BREADCRUMB_COOKIE))->will()->return_(false);
    }

    private function whenIListAllActions() {
        $resource = new IndexResource(
            new Factory(),
            Mockster::mock(WebApplication::class),
            $this->actions,
            Mockster::mock(Menu::class),
            Mockster::mock($this->cookies));

        $resource->doGet($this->makeRequest());
    }
}