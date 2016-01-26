<?php
namespace spec\rtens\domin\fixtures;

use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;

class FakeParameterReader implements ParameterReader {

    /** @var array */
    private $parameters;

    public function __construct(array $parameters = []) {
        $this->parameters = $parameters;
    }

    /**
     * IMPORTANT: files must be properly merged into the parameters
     *
     * @param Parameter $parameter
     * @return mixed The serialized paramater
     */
    public function read(Parameter $parameter) {
        return $this->parameters[$parameter->getName()];
    }

    /**
     * @param Parameter $parameter
     * @return boolean
     */
    public function has(Parameter $parameter) {
        return array_key_exists($parameter->getName(), $this->parameters);
    }
}