<?php
namespace rtens\domin\web;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use watoki\curir\Container;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\Url;
use watoki\factory\Factory;

class IndexResource extends Container {

    /** @var ActionRegistry */
    private $actions;

    /**
     * @param Factory $factory <-
     * @param ActionRegistry $actions <-
     */
    function __construct(Factory $factory, ActionRegistry $actions) {
        parent::__construct($factory);
        $this->actions = $actions;
    }

    public function doGet(WebRequest $request) {
        return [
            'action' => $this->assembleActions($request->getContext())
        ];
    }

    private function assembleActions(Url $base) {
        return $this->actions->getAllActions()->map(function (Action $action, $id) use ($base) {
            return [
                'caption' => $action->caption(),
                'link' => ['href' => $base->appended($id)->toString()]
            ];
        })->asList();
    }
}