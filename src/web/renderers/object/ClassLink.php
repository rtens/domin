<?php
namespace rtens\domin\web\renderers\object;

class ClassLink implements Link {

    private $actionId;
    private $caption;
    private $parameters;
    private $class;

    function __construct($actionId, $class, callable $parameters = null) {
        $this->class = $class;
        $this->actionId = $actionId;
        $this->parameters = $parameters ?: function () {
            return [];
        };
        $this->caption = preg_replace('/(.)([A-Z])/', '$1 $2', ucfirst($this->actionId));
    }

    /**
     * @param object $object
     * @return boolean
     */
    public function handles($object) {
        return is_a($object, $this->class);
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