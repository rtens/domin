<?php
namespace rtens\domin\web\root;

use rtens\domin\ActionRegistry;
use rtens\domin\web\HeadElements;
use rtens\domin\web\menu\Menu;
use watoki\curir\Container;
use watoki\curir\cookie\Cookie;
use watoki\curir\cookie\CookieStore;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\Url;
use watoki\deli\Path;
use watoki\deli\Request;
use watoki\factory\Factory;

class IndexResource extends Container {

    /** @var ActionRegistry */
    private $actions;

    /** @var Menu */
    private $menu;

    /** @var CookieStore */
    private $cookies;

    /**
     * @param Factory $factory <-
     * @param ActionRegistry $actions <-
     * @param Menu $menu <-
     * @param CookieStore $cookies <-
     */
    function __construct(Factory $factory, ActionRegistry $actions, Menu $menu, CookieStore $cookies) {
        parent::__construct($factory);
        $this->actions = $actions;
        $this->menu = $menu;
        $this->cookies = $cookies;
    }

    public function respond(Request $request) {
        if (!$this->isContainerTarget($request)) {
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
        $this->resetBreadCrumbs();
        return [
            'menuItems' => $this->menu->assembleModel($request),
            'action' => $this->assembleActions($request->getContext()),
            'headElements' => [
                (string)HeadElements::jquery(),
                (string)HeadElements::bootstrap(),
                (string)HeadElements::bootstrapJs(),
            ]
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

    private function resetBreadCrumbs() {
        $this->cookies->create(new Cookie([]), ExecuteResource::BREADCRUMB_COOKIE);
    }
}