<?php
namespace rtens\domin\web;

use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;
use watoki\curir\delivery\WebRequest;

class RequestParameterReader implements ParameterReader {

    /** @var WebRequest */
    private $request;

    function __construct(WebRequest $request) {
        $this->request = $request;
    }

    /**
     * @param Parameter $parameter
     * @return null|string The serialized paramater
     */
    public function read(Parameter $parameter) {
        $args = $this->request->getArguments();
        if (!$args->has($parameter->getName())) {
            return null;
        }
        return $args->get($parameter->getName());
    }
}