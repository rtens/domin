<?php
namespace rtens\domin\delivery\web;

use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;

class RequestParameterReader implements ParameterReader {

    /** @var array */
    private $parameters;

    /**
     * @param array $requestParameters IMPORTANT: files must be properly merged into the parameters
     */
    public function __construct(array $requestParameters) {
        $this->parameters = $requestParameters;
    }


    /**
     * @param Parameter $parameter
     * @return string The serialized paramater
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