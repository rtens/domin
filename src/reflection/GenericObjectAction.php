<?php
namespace rtens\domin\reflection;

use rtens\domin\Parameter;
use rtens\domin\reflection\types\TypeFactory;

class GenericObjectAction extends ObjectAction {

    private $execute;
    private $fill;
    private $caption;
    private $description;
    private $paramMap = [];

    public function __construct($class, TypeFactory $types, CommentParser $parser, callable $execute) {
        parent::__construct($class, $types, $parser);
        $this->execute = $execute;
    }

    public function setCaption($caption) {
        $this->caption = $caption;
        return $this;
    }

    public function caption() {
        return $this->caption ?: parent::caption();
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function description() {
        return $this->description ?: parent::description();
    }

    public function setExecute(callable $execute) {
        $this->execute = $execute;
        return $this;
    }

    public function setFill(callable $fill) {
        $this->fill = $fill;
        return $this;
    }

    /**
     * @param callable $callback Filters the return value of execute
     */
    public function setAfterExecute(callable $callback) {
        $oldExecute = $this->execute;
        $this->execute = function ($object) use ($oldExecute, $callback) {
            return $callback(call_user_func($oldExecute, $object));
        };
    }

    /**
     * @param string $name
     * @param callable $map Receives Parameter and returns Parameter
     * @return static
     */
    public function mapParameter($name, callable $map) {
        $this->paramMap[$name] = $map;
        return $this;
    }

    /**
     * @return \rtens\domin\Parameter[]
     * @throws \Exception
     */
    public function parameters() {
        return array_map(function (Parameter $parameter) {
            if (array_key_exists($parameter->getName(), $this->paramMap)) {
                return call_user_func($this->paramMap[$parameter->getName()], $parameter);
            }
            return $parameter;
        }, parent::parameters());
    }

    /**
     * Called by execute() with the instantiated object
     *
     * @param object $object
     * @return mixed
     */
    protected function executeWith($object) {
        return call_user_func($this->execute, $object);
    }

    public function fill(array $parameters) {
        $parameters = parent::fill($parameters);
        if ($this->fill) {
            return call_user_func($this->fill, $parameters);
        }
        return $parameters;
    }
}