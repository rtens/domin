<?php
namespace rtens\domin\delivery\web;

use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;
use watoki\curir\delivery\WebRequest;

class RequestParameterReader implements ParameterReader {

    /** @var WebRequest */
    private $request;

    public function __construct(WebRequest $request) {
        $this->request = $request;
    }

    /**
     * @param Parameter $parameter
     * @return string The serialized paramater
     */
    public function read(Parameter $parameter) {
        return $this->request->getArguments()->get($parameter->getName());
    }

    /**
     * @param Parameter $parameter
     * @return boolean
     */
    public function has(Parameter $parameter) {
        return $this->request->getArguments()->has($parameter->getName());
    }
}