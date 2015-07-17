<?php
namespace rtens\domin\web\renderers\link;

class GenericLink implements Link {

    private $actionId;
    private $caption;
    private $parameters;
    private $handles;
    private $confirmation;

    function __construct($actionId, callable $handles, callable $parameters = null) {
        $this->actionId = $actionId;
        $this->caption = preg_replace('/(.)([A-Z])/', '$1 $2', ucfirst($this->actionId));
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
     * @param mixed $caption
     * @return $this
     */
    public function setCaption($caption) {
        $this->caption = $caption;
        return $this;
    }

    /**
     * @param object $object
     * @return string
     */
    public function caption($object) {
        return $this->caption;
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