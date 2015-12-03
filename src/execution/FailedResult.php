<?php
namespace rtens\domin\execution;

class FailedResult implements ExecutionResult {

    private $exception;

    public function __construct(\Exception $exception) {
        $this->exception = $exception;
    }

    /**
     * @return string|null
     */
    public function getMessage() {
        return $this->exception->getMessage();
    }

    /**
     * @return \Exception
     */
    public function getException() {
        return $this->exception;
    }
}