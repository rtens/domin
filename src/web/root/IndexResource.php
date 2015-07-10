<?php
namespace rtens\domin\web\root;

use rtens\domin\ActionRegistry;
use watoki\curir\Container;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\Url;
use watoki\deli\Path;
use watoki\deli\Request;
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

    public function respond(Request $request) {
        if (!$request->getTarget()->isEmpty()) {
            $request = $request
                ->withTarget(Path::fromString('execute'))
                ->withArgument(ExecuteResource::ACTION_ARG, $request->getTarget()->toString());
        }
        return parent::respond($request);
    }

    /**
     * @param WebRequest $request <-
     * @return array
     */
    public function doGet(WebRequest $request) {
        return [
            'action' => $this->assembleActions($request->getContext())
        ];
    }

    /**
     * @param Url $base
     * @return array
     */
    private function assembleActions(Url $base) {
        $actions = [];
        foreach ($this->actions->getAllActions() as $id => $action) {
            $actions[] = [
                'caption' => $action->caption(),
                'link' => ['href' => $base->appended($id)->toString()]
            ];
        }
        return $actions;
    }
}