<?php
namespace spec\rtens\domin\fixtures;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\Parameter;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\Fixture;
use watoki\reflect\Type;
use watoki\reflect\type\UnknownType;

class ActionFixture extends Fixture {

    /** @var ActionRegistry */
    public $registry;

    /** @var Parameter[][] */
    private $params = [];

    /** @var Action[] */
    public $actions = [];

    public function before() {
        $this->registry = new ActionRegistry();
    }

    public function givenTheAction($id) {
        $this->givenTheAction_Returning($id, null);
    }

    public function givenTheAction_Returning($id, $value) {
        $action = Mockster::of(Action::class);
        $this->actions[$id] = $action;
        $this->registry->add($id, Mockster::mock($action));

        Mockster::stub($action->execute(Argument::any()))->will()->return_($value);
        Mockster::stub($action->caption())->will()->return_(ucfirst($id));
        $this->params[$id] = [];
        Mockster::stub($action->parameters())->will()->forwardTo(function () use ($id) {
            return $this->params[$id];
        });
        Mockster::stub($action->fill(Argument::any()))->will()->forwardTo(function ($params) {
            return $params;
        });
    }

    public function given_FillsParametersWith($id, $filler) {
        Mockster::stub($this->actions[$id]->fill(Argument::any()))->will()->forwardTo($filler);
    }

    public function givenTheAction_FailingWith($id, $message) {
        $this->givenTheAction_Returning($id, null);
        Mockster::stub($this->actions[$id]->execute(Argument::any()))->will()->throw_(new \Exception($message));
    }

    public function given_ExecutesWith($id, $callback) {
        Mockster::stub($this->actions[$id]->execute(Argument::any()))->will()->forwardTo($callback);
    }

    public function given_HasTheDescription($id, $description) {
        Mockster::stub($this->actions[$id]->description())->will()->return_($description);
    }

    public function given_HasTheParameter($id, $name) {
        $this->given_HasTheParameter_OfType($id, $name, 'type of ' . $name);
    }

    public function given_HasTheRequiredParameter($id, $name) {
        $this->params[$id][] = new Parameter($name, new UnknownType($name), true);
    }

    public function given_HasTheParameter_OfType($id, $name, $type) {
        $this->params[$id][] = new Parameter($name, new UnknownType($type));
    }

    public function given_HasTheRequiredParameter_OfATypeMatching($id, $name, $matching) {
        $this->given_HasTheParameter_OfATypeMatching($id, $name, $matching, true);
    }

    public function given_HasTheParameter_OfATypeMatching($id, $name, $matching, $required = false) {
        $type = Mockster::of(Type::class);
        Mockster::stub($type->is($matching))->will()->return_(true);
        $this->params[$id][] = new Parameter($name, Mockster::mock($type), $required);
    }

    public function given_HasTheParameter_WithTheDescription($id, $name, $description) {
        $this->given_HasTheParameter($id, $name);
        $this->params[$id][count($this->params)]->setDescription($description);
    }

    public function givenTheAction_WithTheDescription($string, $string1) {
    }
}