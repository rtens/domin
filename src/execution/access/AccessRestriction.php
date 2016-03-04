<?php
namespace rtens\domin\execution\access;

interface AccessRestriction {

    /**
     * @param string $actionId
     * @return boolean
     */
    public function isRestricted($actionId);

    /**
     * @param string $actionId
     * @param array $parameters
     * @return bool
     */
    public function isExecutionRestricted($actionId, array $parameters);
}