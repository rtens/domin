<?php
namespace rtens\domin\delivery\web\menu;

use rtens\domin\ActionRegistry;
use watoki\collections\Map;
use watoki\curir\delivery\WebRequest;

class Menu {

    private $actions;

    /** @var MenuItem[]MenuGroup[] */
    private $items = [];

    /**
     * @param ActionRegistry $actions <-
     */
    public function __construct(ActionRegistry $actions) {
        $this->actions = $actions;
    }

    public function add(MenuItem $item) {
        $this->items[] = $item;
        return $this;
    }

    public function addGroup(MenuGroup $group) {
        $this->items[] = $group;
        return $this;
    }

    public function assembleModel(WebRequest $request) {
        return array_map(function ($item) use ($request) {
            if ($item instanceof MenuItem) {
                return $this->assembleMenuItem($item, $request);
            } else if ($item instanceof MenuGroup) {
                return [
                    'caption' => $item->getCaption(),
                    'items' => array_map(function (MenuItem $item) use ($request) {
                        return $this->assembleMenuItem($item, $request);
                    }, $item->getItems())
                ];
            } else {
                return null;
            }
        }, $this->items);
    }

    private function assembleMenuItem(MenuItem $item, WebRequest $request) {
        return [
            'caption' => $this->actions->getAction($item->getActionId())->caption(),
            'target' => (string)$request->getContext()
                ->appended($item->getActionId())
                ->withParameters(new Map($item->getParameters()))
        ];
    }
}