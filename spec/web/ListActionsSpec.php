<?php
namespace spec\rtens\domin\web;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\web\IndexResource;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\Url;
use watoki\deli\Path;
use watoki\factory\Factory;

class ListActionsSpec extends StaticTestSuite {

    function noActions() {
        $this->whenIListTheActions();
        $this->thenThereShouldBe_Actions(0);
    }

    function listActions() {
        $this->givenTheAction('foo');
        $this->givenTheAction('bar');

        $this->whenIListTheActions();

        $this->thenThereShouldBe_Actions(2);
        $this->thenAction_ShouldBe(1, 'foo');
        $this->thenAction_ShouldBe(2, 'bar');
    }

    /** @var ActionRegistry */
    private $actions;

    private $model = [];

    protected function before() {
        $this->actions = new ActionRegistry();
    }

    private function whenIListTheActions() {
        $resource = new IndexResource(new Factory(), $this->actions);
        $this->model = $resource->doGet(new WebRequest(Url::fromString('http://domin.dev/base'), new Path()));
    }

    private function thenThereShouldBe_Actions($count) {
        $this->assert->size($this->model['action'], $count);
    }

    private function givenTheAction($id) {
        $action = Mockster::of(Action::class);
        Mockster::stub($action->caption())->will()->return_(ucfirst($id));
        $this->actions->add($id, Mockster::mock($action));
    }

    private function thenAction_ShouldBe($pos, $id) {
        $action = $this->model['action'][$pos - 1];
        $this->assert($action['caption'], ucfirst($id));
        $this->assert($action['link']['href'], "http://domin.dev/base/$id");
    }
}