<?php
namespace rtens\domin\execution;

class MissingParametersResult implements ExecutionResult {

    private $parameters;

    /**
     * @param array $parameters Names of missing parameters
     */
    public function __construct(array $parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }

    public function __toString() {
        return "Missing parameters " . json_encode($this->parameters);
    }
}