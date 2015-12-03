<?php
namespace rtens\domin\execution;

class RenderedResult implements ExecutionResult {

    private $output;

    public function __construct($output) {
        $this->output = $output;
    }

    /**
     * @return mixed
     */
    public function getOutput() {
        return $this->output;
    }
}