<?php namespace rtens\domin\execution\access;

use rtens\domin\AccessControl;

class NoneAccessControl implements AccessControl {

    /**
     * @param string $actionId
     * @return boolean
     */
    public function isVisible($actionId) {
        return true;
    }

    /**
     * @param string $actionId
     * @param mixed[] $parameters indexed by parameter names
     * @return boolean
     */
    public function isExecutionPermitted($actionId, array $parameters) {
        return true;
    }
}