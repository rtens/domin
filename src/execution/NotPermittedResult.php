<?php namespace rtens\domin\execution;

class NotPermittedResult implements ExecutionResult {

    public function __toString() {
        return "[permission denied]";
    }
}