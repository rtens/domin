<?php
namespace rtens\domin\execution;

class ValueResult implements ExecutionResult {

    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }
}