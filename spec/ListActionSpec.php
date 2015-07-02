<?php
namespace spec\rtens\domin;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

/**
 * @property \rtens\scrut\fixtures\ExceptionFixture try <-
 */
class ListActionSpec extends StaticTestSuite {

    function noActions() {
        $this->whenIListTheActions();
        $this->thenItShouldList_Actions(0);
    }

    function actionWithSameId() {
        $this->givenIRegisteredTheAction('foo');
        $this->try->tryTo(function () {
            $this->givenIRegisteredTheAction('foo');
        });
        $this->try->thenTheException_ShouldBeThrown('Action [foo] is already registered.');
    }

    function someActions() {
        $this->givenIRegisteredTheAction('foo');
        $this->givenIRegisteredTheAction('bar');
        $this->whenIListTheActions();
        $this->thenItShouldList_Actions(2);
    }

    private $actions;

    /** @var ActionRegistry */
    private $registry;

    protected function before() {
        $this->registry = new ActionRegistry();
    }

    private function givenIRegisteredTheAction($id) {
        $this->registry->add($id, (new Mockster(Action::class))->mock());
    }

    private function whenIListTheActions() {
        $this->actions = $this->registry->getAllActions();
    }

    private function thenItShouldList_Actions($int) {
        $this->assert->size($this->actions, $int);
    }
}