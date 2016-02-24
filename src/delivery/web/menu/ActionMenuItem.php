<?php namespace rtens\domin\delivery\web\menu;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\Url;

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
            new Element('a', ['href' => (string)Url::relative($this->actionId, $this->parameters)], [$this->getCaption()])
        ]);
    }

    private function getCaption() {
        return $this->caption;
    }
}