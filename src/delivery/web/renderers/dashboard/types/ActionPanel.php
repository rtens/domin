<?php
namespace rtens\domin\delivery\web\renderers\dashboard\types;

class ActionPanel {

    /** @var string */
    private $actionId;

    /** @var array */
    private $parameters;

    /** @var null|string */
    private $maxHeight;

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

    /**
     * @param string $height e.g. "20px", "10em"
     * @return static
     */
    public function setMaxHeight($height) {
        $this->maxHeight = $height;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getMaxHeight() {
        return $this->maxHeight;
    }
}