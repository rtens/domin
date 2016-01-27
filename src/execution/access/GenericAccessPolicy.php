<?php
namespace rtens\domin\execution\access;

class GenericAccessPolicy implements AccessPolicy {

    /** @var string */
    private $actionId;

    /** @var bool */
    private $accessPermitted = true;

    /** @var array[] */
    private $forbiddenParameters = [];

    /**
     * @param string $actionId
     */
    public function __construct($actionId) {
        $this->actionId = $actionId;
    }

    /**
     * @param string $actionId
     * @return boolean
     */
    public function isPermitted($actionId) {
        return $this->accessPermitted || $actionId != $this->actionId;
    }

    /**
     * @param string $actionId
     * @param array $parameters
     * @return bool
     */
    public function isExecutionPemitted($actionId, array $parameters) {
        return  $actionId != $this->actionId || !in_array($parameters, $this->forbiddenParameters);
    }

    /**
     * @return static
     */
    public function denyAccess() {
        $this->accessPermitted = false;
        return $this;
    }

    /**
     * @param array $parameters
     * @return static
     */
    public function denyExecutionWith(array $parameters) {
        $this->forbiddenParameters[] = $parameters;
        return $this;
    }
}