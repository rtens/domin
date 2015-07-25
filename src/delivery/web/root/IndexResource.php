<?php
namespace rtens\domin\delivery\web\root;

use rtens\domin\ActionRegistry;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\menu\Menu;
use rtens\domin\delivery\web\WebApplication;
use watoki\curir\Container;
use watoki\curir\cookie\Cookie;
use watoki\curir\cookie\CookieStore;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\Url;
use watoki\curir\rendering\PhpRenderer;
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

    /** @var WebApplication  */
    private $app;

    /**
     * @param Factory $factory <-
     * @param WebApplication $app <-
     * @param ActionRegistry $actions <-
     * @param Menu $menu <-
     * @param CookieStore $cookies <-
     */
    public function __construct(Factory $factory, WebApplication $app, ActionRegistry $actions, Menu $menu, CookieStore $cookies) {
        parent::__construct($factory);
        $this->actions = $actions;
        $this->menu = $menu;
        $this->cookies = $cookies;
        $this->app = $app;
    }

    public function respond(Request $request) {
        /** @var Url $baseUrl */
        $baseUrl = $request->getContext();
        $this->app->registerRenderers($baseUrl);
        $this->app->registerFields();

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
                'description' => explode("\n\n", $action->description())[0],
                'link' => ['href' => $base->appended($id)->toString()]
            ];
        }
        return $actions;
    }

    private function resetBreadCrumbs() {
        $this->cookies->create(new Cookie([]), ExecuteResource::BREADCRUMB_COOKIE);
    }

    protected function createDefaultRenderer() {
        return new PhpRenderer();
    }
}