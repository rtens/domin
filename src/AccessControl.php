<?php namespace rtens\domin;

interface AccessControl {

    /**
     * @param string $actionId
     * @return boolean
     */
    public function isVisible($actionId);

    /**
     * @param string $actionId
     * @param mixed[] $parameters indexed by parameter names
     * @return boolean
     */
    public function isExecutionPermitted($actionId, array $parameters);
}