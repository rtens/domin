<?php
namespace rtens\domin\execution\access;

interface AccessPolicy {

    /**
     * @param string $actionId
     * @return boolean
     */
    public function isPermitted($actionId);

    /**
     * @param string $actionId
     * @param array $parameters
     * @return bool
     */
    public function isExecutionPemitted($actionId, array $parameters);
}