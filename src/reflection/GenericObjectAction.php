<?php
namespace rtens\domin\reflection;

use rtens\domin\reflection\types\TypeFactory;

class GenericObjectAction extends ObjectAction {

    /** @var callable */
    private $execute;

    /** @var GenericAction */
    private $generic;

    public function __construct($class, TypeFactory $types, CommentParser $parser, callable $execute) {
        parent::__construct($class, $types, $parser);
        $this->execute = $execute;
        $this->generic = new GenericAction($this);
        $this->generic
            ->setCaption(parent::caption())
            ->setDescription(parent::description())
            ->setParameters(parent::parameters())
            ->setExecute(function ($parameters) {
                return parent::execute($parameters);
            })
            ->setFill(function ($parameters) {
                return $parameters;
            });
    }

    public function generic() {
        return $this->generic;
    }

    protected function executeWith($object) {
        return call_user_func($this->execute, $object);
    }

    public function execute(array $parameters) {
        return $this->generic->execute($parameters);
    }

    public function caption() {
        return $this->generic->caption();
    }

    public function description() {
        return $this->generic->description();
    }

    public function parameters() {
        return $this->generic->parameters();
    }

    public function fill(array $parameters) {
        return $this->generic->fill(parent::fill($parameters));
    }
}