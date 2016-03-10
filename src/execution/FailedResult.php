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

    public function getDetails() {
        $details = '';
        $exception = $this->exception;

        while ($exception) {
            $details .= $details ? 'Caused by: ' : 'Exception: ';
            $details .= $this->exception->getMessage() . "\n" .
                "In " . $this->exception->getFile() . '(' . $this->exception->getLine() . ")\n" .
                $this->exception->getTraceAsString() . "\n\n";

            $exception = $exception->getPrevious();
        }
        return $details;
    }
}