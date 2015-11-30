<?php
namespace rtens\domin\reflection;

use rtens\domin\Action;
use rtens\domin\Parameter;

class GenericAction implements Action {

    /** @var null|callable */
    private $execute;

    /** @var null|callable */
    private $afterExecute;

    /** @var null|callable */
    private $fill;

    /** @var null|string */
    private $caption;

    /** @var null|string */
    private $description;

    /** @var array|callable[] by parameter name */
    private $paramMap = [];

    /** @var null|Parameter[] */
    private $parameters;

    /** @var Action */
    private $action;

    public function __construct(Action $action) {
        $this->action = $action;
    }

    public function setCaption($caption) {
        $this->caption = $caption;
        return $this;
    }

    public function caption() {
        return !is_null($this->caption) ? $this->caption : $this->action->caption();
    }

    public function setDescription($description) {
        $this->description = $description ?: '';
        return $this;
    }

    public function description() {
        return !is_null($this->description) ? $this->description : $this->action->description();
    }

    public function setExecute(callable $execute) {
        $this->execute = $execute;
        return $this;
    }

    public function execute(array $parameters) {
        if ($this->execute) {
            $result = call_user_func($this->execute, $parameters);
        } else {
            $result = $this->action->execute($parameters);
        }

        if ($this->afterExecute) {
            $result = call_user_func($this->afterExecute, $result);
        }

        return $result;
    }

    /**
     * @param callable $callback Filters the return value of execute
     * @return static
     */
    public function setAfterExecute(callable $callback) {
        $this->afterExecute = $callback;
        return $this;
    }

    /**
     * @param Parameter[] $parameters
     * @return static
     */
    public function setParameters(array $parameters) {
        $this->parameters = $parameters;
        return $this;
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
        }, !is_null($this->parameters) ? $this->parameters : $this->action->parameters());
    }

    public function setFill(callable $fill) {
        $this->fill = $fill;
        return $this;
    }

    public function fill(array $parameters) {
        if ($this->fill) {
            $parameters = call_user_func($this->fill, $parameters);
        } else {
            $parameters = $this->action->fill($parameters);
        }
        return $parameters;
    }

    /**
     * @return Action
     */
    public function action() {
        return $this->action;
    }
}