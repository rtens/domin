<?php
namespace rtens\domin\execution;

class RedirectResult implements ExecutionResult {

    private $actionId;
    private $parameters;

    /**
     * @param string $actionId
     * @param array $parameters
     */
    public function __construct($actionId, $parameters = []) {
        $this->actionId = $actionId;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getActionId() {
        return $this->actionId;
    }

    /**
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }

    public function __toString() {
        return $this->actionId . ': ' . json_encode($this->parameters);
    }
}