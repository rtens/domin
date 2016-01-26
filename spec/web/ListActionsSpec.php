<?php
namespace spec\rtens\domin\delivery\web;

use rtens\domin\delivery\web\BreadCrumbsTrail;
use rtens\domin\delivery\web\menu\Menu;
use rtens\domin\delivery\web\resources\ActionListResource;
use rtens\domin\delivery\web\WebApplication;
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

    ################################################################################

    /** @var Factory */
    private $factory;

    private $response;

    protected function before() {
        $this->factory = new Factory();
        $this->factory->setSingleton($this->action->registry);
        $this->factory->setSingleton(new Menu($this->action->registry));
    }

    private function whenIListTheActions() {
        /** @var WebApplication $app */
        $app = $this->factory->getInstance(WebApplication::class);
        $app->prepare();

        $execution = new ActionListResource($app, new BreadCrumbsTrail(new FakeParameterReader(), []));
        $this->response = $execution->handleGet();
    }

    private function thenThereShouldBe_Actions($count) {
        $this->assert(preg_match_all('/class="list-group-item"/', $this->response), $count);
    }

    private function thenThereShouldBeAnAction($id) {
        $this->assert->contains($this->response,
            sprintf('<a href="%s" class="list-group-item">' . "\n" . '            %s', $id, ucfirst($id)));
    }
}