<?php
namespace rtens\domin\delivery\web\resources;

use rtens\domin\delivery\web\BreadCrumbsTrail;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebApplication;

class ActionListResource {

    /** @var WebApplication  */
    private $app;

    /** @var BreadCrumbsTrail */
    private $crumbs;

    public function __construct(WebApplication $app, BreadCrumbsTrail $crumbs) {
        $this->app = $app;
        $this->crumbs = $crumbs;
    }

    /**
     * @return string
     */
    public function handleGet() {
        $this->app->prepare();
        $this->crumbs->reset();

        global $model;
        $model = [
            'name' => $this->app->name,
            'menu' => $this->app->menu->render(),
            'action' => $this->assembleActions(),
            'headElements' => [
                (string)HeadElements::jquery(),
                (string)HeadElements::bootstrap(),
                (string)HeadElements::bootstrapJs(),
            ]
        ];
        return eval('?>' . file_get_contents(__DIR__ . '/ActionListTemplate.html.php'));
    }

    private function assembleActions() {
        $actions = [];
        foreach ($this->app->actions->getAllActions() as $id => $action) {
            $actions[] = [
                'caption' => $action->caption(),
                'description' => $this->app->parser->shorten($action->description()),
                'link' => ['href' => $id]
            ];
        }
        return $actions;
    }
}