<?php namespace rtens\domin\delivery\web\menu;

use rtens\domin\delivery\web\Element;

class ActionMenuItem implements MenuItem {

    /** @var string */
    private $actionId;

    /** @var array */
    private $parameters;

    /** @var string */
    private $caption;

    public function __construct($caption, $actionId, $parameters = []) {
        $this->actionId = $actionId;
        $this->parameters = $parameters;
        $this->caption = $caption;
    }

    public function render() {
        return new Element('li', [], [
            new Element('a', ['href' => $this->getTarget()], [$this->getCaption()])
        ]);
    }

    private function getTarget() {
        $target = $this->actionId;
        if ($this->parameters) {
            $keyValues = [];
            foreach ($this->parameters as $key => $value) {
                $keyValues[] = urlencode($key) . '=' . urlencode($value);
            }
            $target .= '?' . implode('&', $keyValues);
        }
        return $target;
    }

    private function getCaption() {
        return $this->caption;
    }
}