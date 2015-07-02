<?php
namespace rtens\domin\execution;

class NoResult implements ExecutionResult {

    public function __toString() {
        return '[no result]';
    }
}