<?php
namespace rtens\domin\web\renderers\object;

class ClassLink implements Link {

    private $actionId;
    private $caption;
    private $parameters;
    private $class;
    private $handles;

    function __construct($class, $actionId, callable $parameters = null) {
        $this->class = $class;
        $this->actionId = $actionId;
        $this->caption = preg_replace('/(.)([A-Z])/', '$1 $2', ucfirst($this->actionId));
        $this->parameters = $parameters ?: function () {
            return [];
        };
        $this->handles = function ($object) {
            return is_a($object, $this->class);
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
}