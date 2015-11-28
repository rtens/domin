<?php
namespace rtens\domin\reflection;

use rtens\domin\Parameter;

class GenericMethodAction extends MethodAction {

    private $afterExecute;
    private $caption;
    private $fill;
    private $description;
    private $paramMap = [];

    /**
     * @param callable $callback Filter for return value of execute()
     * @return static
     */
    public function setAfterExecute(callable $callback) {
        $this->afterExecute = $callback;
        return $this;
    }

    public function execute(array $parameters) {
        $return = parent::execute($parameters);
        if ($this->afterExecute) {
            $return = call_user_func($this->afterExecute, $return);
        }
        return $return;
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

    public function setFill(callable $callback) {
        $this->fill = $callback;
        return $this;
    }

    public function fill(array $parameters) {
        $parameters = parent::fill($parameters);
        if ($this->fill) {
            $parameters = call_user_func($this->fill, $parameters);
        }
        return $parameters;
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
}