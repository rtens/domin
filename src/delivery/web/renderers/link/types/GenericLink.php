<?php
namespace rtens\domin\delivery\web\renderers\link\types;

use rtens\domin\delivery\web\renderers\link\Link;

class GenericLink implements Link {

    private $actionId;
    private $parameters;
    private $handles;
    private $confirmation;

    public function __construct($actionId, callable $handles, callable $parameters = null) {
        $this->actionId = $actionId;
        $this->handles = $handles;
        $this->parameters = $parameters ?: function () {
            return [];
        };
    }

    public function setHandles(callable $handles) {
        $this->handles = $handles;
        return $this;
    }

    /**
     * @param object $object
     * @return boolean
     */
    public function handles($object) {
        return call_user_func($this->handles, $object);
    }

    /**
     * @return string
     */
    public function actionId() {
        return $this->actionId;
    }

    /**
     * @param object $object
     * @return array|mixed[] Values indexed by parameter names
     */
    public function parameters($object) {
        return call_user_func($this->parameters, $object);
    }

    /**
     * Message that needs to be confirmed before the action can be executed
     * @param string|null $confirmation
     * @return $this
     */
    public function setConfirmation($confirmation) {
        $this->confirmation = $confirmation;
        return $this;
    }

    /**
     * A message that needs to be confirmed before the action can be executed (or null if not required)
     * @return string|null
     */
    public function confirm() {
        return $this->confirmation;
    }

}