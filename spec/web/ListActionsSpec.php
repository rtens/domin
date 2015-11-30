<?php
namespace spec\rtens\domin\delivery\web;

use rtens\domin\AccessControl;
use rtens\domin\delivery\web\menu\Menu;
use rtens\domin\delivery\web\root\IndexResource;
use rtens\domin\delivery\web\WebAccessControl;
use rtens\domin\delivery\web\WebApplication;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

/**
 * @property \spec\rtens\domin\fixtures\ActionFixture action <-
 * @property \spec\rtens\domin\fixtures\WebFixture web <-
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
        $this->thenAction_ShouldBe(1, 'foo');
        $this->thenAction_ShouldBe(2, 'bar');
    }

    function restrictAccess() {
        $this->action->givenTheAction('foo');
        $this->action->givenTheAction('bar');

        $access = Mockster::of(AccessControl::class);
        Mockster::stub($access->isVisible('bar'))->will()->return_(true);

        WebApplication::init(function (WebApplication $application) use ($access) {
            $application->restrictAccess(WebAccessControl::factory(Mockster::mock($access)));
        }, $this->web->factory);

        $this->whenIListTheActions();

        $this->thenThereShouldBe_Actions(1);
        $this->thenAction_ShouldBe(1, 'bar');
    }

    ################################################################################

    protected function before() {
        parent::before();
        $this->web->factory->setSingleton($this->action->registry);
        $this->web->factory->setSingleton(new Menu($this->action->registry));
    }

    private function whenIListTheActions() {
        $this->web->whenIGet_From('', IndexResource::class);
    }

    private function thenThereShouldBe_Actions($count) {
        $this->assert->size($this->web->model['action'], $count);
    }

    private function thenAction_ShouldBe($pos, $id) {
        $action = $this->web->model['action'][$pos - 1];
        $this->assert($action['caption'], ucfirst($id));
        $this->assert($action['link']['href'], "http://example.com/base/$id");
    }
}