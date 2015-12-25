<?php
namespace rtens\domin\delivery\web\root;

use rtens\domin\Action;
use rtens\domin\delivery\web\ActionForm;
use rtens\domin\delivery\web\ActionResult;
use rtens\domin\delivery\web\BreadCrumbs;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebApplication;
use watoki\curir\cookie\CookieStore;
use watoki\curir\delivery\WebRequest;
use watoki\curir\delivery\WebResponse;
use watoki\curir\error\HttpError;
use watoki\curir\rendering\PhpRenderer;
use watoki\curir\Resource;
use watoki\factory\Factory;

class ExecuteResource extends Resource {

    const ACTION_ARG = '__action';

    /** @var CookieStore */
    private $cookies;

    /**
     * @param Factory $factory <-
     * @param WebApplication $app <-
     * @param CookieStore $cookies <-
     */
    public function __construct(Factory $factory, WebApplication $app, CookieStore $cookies) {
        parent::__construct($factory);
        $this->app = $app;
        $this->cookies = $cookies;
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
     * @param string $__action
     * @param WebRequest $__request <-
     * @return array
     * @throws \Exception
     */
    public function doGet($__action, WebRequest $__request) {
        return $this->doExecute($__action, $__request);
    }

    /**
     * @param string $__action
     * @param WebRequest $__request <-
     * @return array
     */
    public function doPost($__action, WebRequest $__request) {
        return $this->doExecute($__action, $__request, true);
    }

    private function doExecute($actionId, WebRequest $request, $mayBeModifying = false) {
        $action = $this->getAction($actionId);

        $crumbs = new BreadCrumbs($this->cookies, $request);
        $form = new ActionForm($request, $this->app, $action, $actionId);

        $headElements = array_merge(
            self::baseHeadElements(),
            $form->getHeadElements()
        );

        if ($mayBeModifying || !$action->isModifying()) {
            $result = new ActionResult($request, $this->app, $action, $actionId, $crumbs);
            $headElements = array_merge($headElements, $result->getHeadElements());
        }

        return [
            'name' => $this->app->name,
            'caption' => $action->caption(),
            'menu' => $this->app->menu->render($request),
            'breadcrumbs' => $crumbs->readCrumbs(),
            'action' => $form->getModel(),
            'result' => isset($result) ? $result->getModel() : null,
            'headElements' => HeadElements::filter($headElements),
        ];
    }

    protected function createDefaultRenderer() {
        return new PhpRenderer();
    }

    /**
     * @param $actionId
     * @return Action
     * @throws HttpError
     */
    private function getAction($actionId) {
        try {
            return $this->app->actions->getAction($actionId);
        } catch (\Exception $e) {
            throw new HttpError(WebResponse::STATUS_NOT_FOUND, "Action does not exist.",
                "Action [$actionId] is not registered.", 0, $e);
        }
    }
}