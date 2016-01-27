<?php
namespace rtens\domin\delivery\web\resources;

use rtens\domin\Action;
use rtens\domin\delivery\web\BreadCrumbsTrail;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebApplication;

class ActionListResource {
    const GROUP_ALL = 'All';

    /** @var WebApplication */
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
        $this->crumbs->reset();

        return (new Template(__DIR__ . '/ActionListTemplate.html.php'))
            ->render([
                'name' => $this->app->name,
                'menu' => $this->app->menu->render(),
                'actions' => $this->assembleAllActions(),
                'headElements' => [
                    (string)HeadElements::jquery(),
                    (string)HeadElements::bootstrap(),
                    (string)HeadElements::bootstrapJs(),
                ]
            ]);
    }

    private function assembleAllActions() {
        return array_merge($this->assembleActionGroups(), [
            self::GROUP_ALL => $this->assembleActions($this->app->actions->getAllActions())
        ]);
    }

    private function assembleActionGroups() {
        $groups = [];
        foreach ($this->app->groups->getGroups() as $group) {
            $groups[$group] = $this->assembleActions($this->app->groups->getActionsOf($group));
        }
        return $groups;
    }

    /**
     * @param Action[] $actionsById
     * @return array
     */
    private function assembleActions($actionsById) {
        $actions = [];
        foreach ($actionsById as $id => $action) {
            if ($this->app->access->isPermitted($id)) {
                $actions[] = [
                    'caption' => $action->caption(),
                    'description' => $this->app->parser->shorten($action->description()),
                    'link' => ['href' => $id]
                ];
            }
        }
        return $actions;
    }
}