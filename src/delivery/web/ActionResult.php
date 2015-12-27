<?php
namespace rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\execution\ExecutionResult;
use rtens\domin\execution\FailedResult;
use rtens\domin\execution\MissingParametersResult;
use rtens\domin\execution\NoResult;
use rtens\domin\execution\NotPermittedResult;
use rtens\domin\execution\RedirectResult;
use rtens\domin\execution\ValueResult;
use rtens\domin\Executor;
use watoki\collections\Map;
use watoki\curir\delivery\WebRequest;

class ActionResult {

    /** @var array */
    private $model;

    /** @var Element[] */
    private $headElements = [];

    /**
     * @param WebRequest $request
     * @param WebApplication $app
     * @param Action $action
     * @param string $actionId
     * @param BreadCrumbs $crumbs
     */
    public function __construct(WebRequest $request, WebApplication $app, Action $action, $actionId, BreadCrumbs $crumbs) {
        $reader = new RequestParameterReader($request);

        $executor = new Executor($app->actions, $app->fields, $reader);
        $executor->restrictAccess($app->getAccessControl($request));
        $result = $executor->execute($actionId);

        $this->model = $this->assembleResult($result, $app, $request, $crumbs, $action, $actionId);
    }

    private function assembleResult(ExecutionResult $result, WebApplication $app, WebRequest $request,
                                    BreadCrumbs $crumbs, Action $action, $actionId) {
        $model = [
            'error' => null,
            'missing' => null,
            'success' => null,
            'redirect' => null,
            'output' => null
        ];

        if ($result instanceof FailedResult) {
            $model['error'] = htmlentities($result->getMessage());

        } else if ($result instanceof MissingParametersResult) {
            $model['missing'] = $result->getMissingNames();

        } else if ($result instanceof NoResult) {
            $model['success'] = true;
            $model['redirect'] = $crumbs->getLastCrumb();

        } else if ($result instanceof NotPermittedResult) {
            $model['error'] = 'You are not permitted to execute this action.';
            $model['redirect'] = $app->getAccessControl($request)->acquirePermission();

        } else if ($result instanceof RedirectResult) {
            $model['success'] = true;
            $model['redirect'] = $request->getContext()
                ->appended($result->getActionId())
                ->withParameters(new Map($result->getParameters()));

        } else if ($result instanceof ValueResult) {
            $value = $result->getValue();
            $renderer = $app->renderers->getRenderer($value);

            if ($renderer instanceof WebRenderer) {
                $this->headElements = $renderer->headElements($value);
            }
            $model['output'] = $renderer->render($value);

            if (!$action->isModifying()) {
                $crumbs->updateCrumbs($action, $actionId);
            }
        }

        return $model;
    }

    public function getModel() {
        return $this->model;
    }

    public function getHeadElements() {
        return $this->headElements;
    }

    public function wasExecuted() {
        return !$this->model['error'] && !$this->model['missing'];
    }
}