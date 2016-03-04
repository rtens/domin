<?php
namespace rtens\domin\execution\access;

class GenericAccessRestriction implements AccessRestriction {

    /** @var string */
    private $actionId;

    /** @var bool */
    private $accessRestricted = false;

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
    public function isRestricted($actionId) {
        return $actionId == $this->actionId && $this->accessRestricted;
    }

    /**
     * @param string $actionId
     * @param array $parameters
     * @return bool
     */
    public function isExecutionRestricted($actionId, array $parameters) {
        return  $actionId == $this->actionId && in_array($parameters, $this->forbiddenParameters);
    }

    /**
     * @return static
     */
    public function denyAccess() {
        $this->accessRestricted = true;
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