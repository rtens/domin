<?php
namespace rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\execution\FailedResult;
use rtens\domin\execution\MissingParametersResult;
use rtens\domin\execution\RedirectResult;
use rtens\domin\execution\ValueResult;
use rtens\domin\Executor;

class ActionResult {

    /** @var array */
    private $model;

    /** @var Element[] */
    private $headElements = [];

    /** @var Action */
    private $action;

    /** @var string */
    private $actionId;

    /** @var BreadCrumbsTrail */
    private $crumbs;

    /** @var Executor */
    private $executor;

    /** @var RendererRegistry */
    private $renderers;

    public function __construct(Executor $executor, RendererRegistry $renderers, Action $action, $actionId, BreadCrumbsTrail $crumbs) {
        $this->action = $action;
        $this->actionId = $actionId;
        $this->crumbs = $crumbs;
        $this->executor = $executor;
        $this->renderers = $renderers;
    }

    public function getModel() {
        $this->executeFirst();
        return $this->model;
    }

    public function getHeadElements() {
        $this->executeFirst();
        return $this->headElements;
    }

    public function wasExecuted() {
        $this->executeFirst();
        return !$this->model['error'] && !$this->model['missing'];
    }

    private function executeFirst() {
        if (!$this->model) {
            $this->execute();
        }
    }

    private function execute() {
        $result = $this->executor->execute($this->actionId);

        $this->model = [
            'error' => null,
            'missing' => null,
            'success' => null,
            'redirect' => null,
            'output' => null
        ];

        call_user_func([$this, 'handle' . (new \ReflectionClass($result))->getShortName()], $result);
    }

    protected function handleFailedResult(FailedResult $result) {
        $this->model['error']['message'] = htmlentities($result->getMessage());
        $this->model['error']['details'] = htmlentities($result->getDetails());
    }

    protected function handleMissingParametersResult(MissingParametersResult $result) {
        $this->model['missing'] = $result->getMissingNames();
    }

    protected function handleNoResult() {
        $this->model['success'] = true;

        if ($this->crumbs->hasCrumbs()) {
            $this->model['redirect'] = $this->crumbs->getLastCrumb()->getTarget();
        }
    }

    protected function handleNotPermittedResult() {
        $this->model['error'] = 'You are not permitted to execute this action.';
    }

    protected function handleRedirectResult(RedirectResult $result) {
        $this->model['success'] = true;
        $this->model['redirect'] = $result->getUrl();
    }

    protected function handleValueResult(ValueResult $result) {
        $value = $result->getValue();
        $renderer = $this->renderers->getRenderer($value);

        if ($renderer instanceof WebRenderer) {
            $this->headElements = $renderer->headElements($value);
        }
        $this->model['output'] = $renderer->render($value);

        if (!$this->action->isModifying()) {
            $this->crumbs->updateCrumbs($this->action, $this->actionId);
        }
    }
}