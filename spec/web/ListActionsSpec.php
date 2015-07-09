<?php
namespace spec\rtens\domin\web;

use rtens\domin\web\root\IndexResource;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\Url;
use watoki\deli\Path;
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
        $this->thenAction_ShouldBe(1, 'foo');
        $this->thenAction_ShouldBe(2, 'bar');
    }

    private $model = [];

    private function whenIListTheActions() {
        $resource = new IndexResource(new Factory(), $this->action->registry);
        $this->model = $resource->doGet(new WebRequest(Url::fromString('http://domin.dev/base'), new Path()));
    }

    private function thenThereShouldBe_Actions($count) {
        $this->assert->size($this->model['action'], $count);
    }

    private function thenAction_ShouldBe($pos, $id) {
        $action = $this->model['action'][$pos - 1];
        $this->assert($action['caption'], ucfirst($id));
        $this->assert($action['link']['href'], "http://domin.dev/base/$id");
    }
}