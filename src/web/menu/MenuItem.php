<?php
namespace rtens\domin\web\menu;

class MenuItem {

    private $actionId;
    private $parameters;

    function __construct($actionId, $parameters = []) {
        $this->actionId = $actionId;
        $this->parameters = $parameters;
    }

    public function getActionId() {
        return $this->actionId;
    }

    public function getParameters() {
        return $this->parameters;
    }
}