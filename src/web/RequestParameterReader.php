<?php
namespace rtens\domin\web;

use rtens\domin\delivery\ParameterReader;
use watoki\curir\delivery\WebRequest;

class RequestParameterReader implements ParameterReader {

    /** @var WebRequest */
    private $request;

    function __construct(WebRequest $request) {
        $this->request = $request;
    }

    /**
     * @param string $name
     * @return string|null The serialized paramater
     */
    public function read($name) {
        $args = $this->request->getArguments();
        if (!$args->has($name)) {
            return null;
        }
        return $args->get($name);
    }
}