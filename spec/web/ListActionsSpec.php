<?php
namespace spec\rtens\domin\delivery\web;

use rtens\domin\delivery\web\menu\Menu;
use rtens\domin\delivery\web\root\IndexResource;
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

    private function whenIListTheActions() {
        $this->web->factory->setSingleton($this->action->registry);
        $this->web->factory->setSingleton(new Menu($this->action->registry));
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