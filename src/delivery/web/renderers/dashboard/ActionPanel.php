<?php
namespace rtens\domin\delivery\web\renderers\dashboard;

class ActionPanel {

    /** @var string */
    private $actionId;

    /** @var array */
    private $parameters;

    public function __construct($actionId, array $parameters = []) {
        $this->actionId = $actionId;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getActionId() {
        return $this->actionId;
    }

    /**
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }
}