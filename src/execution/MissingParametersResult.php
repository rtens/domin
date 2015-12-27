<?php
namespace rtens\domin\execution;

class MissingParametersResult implements ExecutionResult {

    private $parameters;

    /**
     * @param \Exception[] $parameters
     */
    public function __construct(array $parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @return string[]
     */
    public function getMissingNames() {
        return array_keys($this->parameters);
    }

    /**
     * @param string $parameterName
     * @return \Exception
     */
    public function getException($parameterName) {
        return $this->parameters[$parameterName];
    }
}