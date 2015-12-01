<?php namespace rtens\domin\delivery\web\menu;

use rtens\domin\delivery\web\Element;
use watoki\collections\Map;
use watoki\curir\delivery\WebRequest;

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

    public function render(WebRequest $request) {
        return new Element('li', [], [
            new Element('a', ['href' => $this->getTarget($request)], [$this->getCaption()])
        ]);
    }

    private function getTarget(WebRequest $request) {
        return (string)$request->getContext()
            ->appended($this->actionId)
            ->withParameters(new Map($this->parameters));
    }

    private function getCaption() {
        return $this->caption;
    }
}