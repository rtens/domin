<?php
namespace spec\rtens\domin\delivery\web;

use rtens\domin\delivery\web\BreadCrumbsTrail;
use rtens\domin\delivery\web\home\ListActions;
use rtens\domin\delivery\web\resources\ExecutionResource;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\execution\access\AccessControl;
use rtens\domin\execution\access\GenericAccessPolicy;
use rtens\scrut\tests\statics\StaticTestSuite;
use spec\rtens\domin\fixtures\FakeParameterReader;
use watoki\factory\Factory;

/**
 * @property \spec\rtens\domin\fixtures\ActionFixture action <-
 */
class ListActionsSpec extends StaticTestSuite {

    function noActions() {
        $this->whenIListTheActions();
        $this->thenThereShouldBe_Actions(0);
    }

    function listActions() {
        $this->action->givenTheAction('foo');
        $this->action->givenTheAction('bar');

        $this->whenIListTheActions();

        $this->thenThereShouldBe_Actions(2);
        $this->thenThereShouldBeAnAction('foo');
        $this->thenThereShouldBeAnAction('bar');
    }

    function hideDeniedActions() {
        $this->action->givenTheAction('foo');
        $this->action->givenTheAction('bar');

        $this->access->add((new GenericAccessPolicy('foo'))->denyAccess());

        $this->whenIListTheActions();

        $this->thenThereShouldBe_Actions(1);
        $this->thenThereShouldBeAnAction('bar');
    }

    function actionsInGroups() {
        $this->action->givenTheAction('foo');
        $this->action->givenTheAction('bar');

        $this->app->groups->put('foo', 'A');
        $this->app->groups->put('bar', 'A');
        $this->app->groups->put('bar', 'B');

        $this->whenIListTheActions();

        $this->thenThereShouldBe_Actions(5);
        $this->thenThereShouldBeAnActionGroup('All');
        $this->thenThereShouldBeAnActionGroup('A');
        $this->thenThereShouldBeAnActionGroup('B');
    }

    ################################################################################

    /** @var Factory */
    private $factory;

    /** @var AccessControl */
    private $access;

    private $response;

    /** @var WebApplication */
    private $app;

    protected function before() {
        $this->factory = new Factory();
        $this->factory->setSingleton($this->action->registry);
        $this->access = $this->factory->setSingleton(new AccessControl());

        $this->app = $this->factory->getInstance(WebApplication::class);
        $this->app->prepare();
    }

    private function whenIListTheActions() {
        $this->app->actions->add('index', new ListActions($this->app->actions, $this->app->groups, $this->app->access, $this->app->parser));

        $reader = new FakeParameterReader();
        $crumbs = new BreadCrumbsTrail($reader, []);

        $resource = new ExecutionResource($this->app, $reader, $crumbs);
        $this->response = $resource->handleGet('index');
    }

    private function thenThereShouldBe_Actions($count) {
        $this->assert(preg_match_all('/class="list-group-item"/', $this->response), $count);
    }

    private function thenThereShouldBeAnAction($id) {
        $this->assert->contains(preg_replace("/\n\s+/", "", $this->response),
            sprintf('<a href="%s" class="list-group-item">%s', $id, ucfirst($id)));
    }

    private function thenThereShouldBeAnActionGroup($name) {
        $this->assert->contains($this->response, "$name\n</h2>");
    }
}