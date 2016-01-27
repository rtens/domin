<?php
namespace rtens\domin\delivery\web\resources;

use rtens\domin\Action;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\web\ActionForm;
use rtens\domin\delivery\web\ActionResult;
use rtens\domin\delivery\web\BreadCrumb;
use rtens\domin\delivery\web\BreadCrumbsTrail;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\Executor;

class ExecutionResource {

    /** @var WebApplication */
    private $app;

    /** @var BreadCrumbsTrail */
    private $crumbs;

    /** @var ParameterReader */
    private $reader;

    public function __construct(WebApplication $app, ParameterReader $reader, BreadCrumbsTrail $crumbs) {
        $this->app = $app;
        $this->crumbs = $crumbs;
        $this->reader = $reader;
    }

    private static function baseHeadElements() {
        return [
            HeadElements::jquery(),
            HeadElements::jqueryUi(), // not actually needed but it needs to be included before bootstrap.js too avoid conflicts
            HeadElements::bootstrap(),
            HeadElements::bootstrapJs(),
        ];
    }

    /**
     * @param string $actionId
     * @return string
     */
    public function handleGet($actionId) {
        return $this->doExecute($actionId, false);
    }

    /**
     * @param string $actionId
     * @return string
     * @throws \Exception
     */
    public function handlePost($actionId) {
        return $this->doExecute($actionId, true);
    }

    private function doExecute($actionId, $mayBeModifying) {
        $action = $this->getAction($actionId);
        $headElements = self::baseHeadElements();

        $form = new ActionForm($this->reader, $this->app->fields, $action, $actionId);
        $headElements = array_merge($headElements, $form->getHeadElements());

        if ($mayBeModifying || !$action->isModifying()) {
            $executor = new Executor($this->app->actions, $this->app->fields, $this->reader);
            $result = new ActionResult($executor, $this->app->renderers, $action, $actionId, $this->crumbs);
            $headElements = array_merge($headElements, $result->getHeadElements());
        }

        return (new Template(__DIR__ . '/ExecutionTemplate.html.php'))
            ->render([
                'name' => $this->app->name,
                'caption' => $action->caption(),
                'menu' => $this->app->menu->render(),
                'breadcrumbs' => $this->assembleBreadCrumbs(),
                'action' => $form->getModel(),
                'result' => isset($result) ? $result->getModel() : null,
                'headElements' => HeadElements::filter($headElements),
                'executed' => isset($result) && $result->wasExecuted()
            ]);
    }

    /**
     * @param $actionId
     * @return Action
     * @throws \Exception
     */
    private function getAction($actionId) {
        try {
            return $this->app->actions->getAction($actionId);
        } catch (\Exception $e) {
            throw new \Exception("Action [$actionId] is not registered.", 0, $e);
        }
    }

    private function assembleBreadCrumbs() {
        return array_map(function (BreadCrumb $crumb) {
            return [
                'target' => $crumb->getTarget(),
                'caption' => $crumb->getCaption()
            ];
        }, $this->crumbs->getCrumbs());
    }
}