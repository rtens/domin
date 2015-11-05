<?php
namespace rtens\domin\delivery\web\root;

use rtens\domin\Action;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\RequestParameterReader;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\delivery\web\WebExecutor;
use rtens\domin\delivery\web\WebField;
use rtens\domin\execution\ExecutionResult;
use rtens\domin\execution\FailedResult;
use rtens\domin\execution\MissingParametersResult;
use rtens\domin\execution\NoResult;
use rtens\domin\execution\RedirectResult;
use rtens\domin\execution\RenderedResult;
use watoki\collections\Map;
use watoki\curir\cookie\Cookie;
use watoki\curir\cookie\CookieStore;
use watoki\curir\delivery\WebRequest;
use watoki\curir\rendering\PhpRenderer;
use watoki\curir\Resource;
use watoki\factory\Factory;

class ExecuteResource extends Resource {

    const ACTION_ARG = '__action';
    const BREADCRUMB_COOKIE = 'domin_trail';

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
     * @param WebRequest $request <-
     * @return array
     */
    public function doPost($__action, WebRequest $request) {
        return $this->doGet($__action, $request);
    }

    /**
     * @param string $__action
     * @param WebRequest $request <-
     * @return array
     * @throws \Exception
     */
    public function doGet($__action, WebRequest $request) {
        $fields = [
            'headElements' => self::baseHeadElements(),
            'fields' => []
        ];
        $caption = 'Error';
        $description = null;
        $crumbs = [];

        $reader = new RequestParameterReader($request);

        try {
            $action = $this->app->actions->getAction($__action);
            $caption = $action->caption();
            $description = $action->description();

            $executor = new WebExecutor($this->app->actions, $this->app->fields, $this->app->renderers, $reader);
            $result = $executor->execute($__action);

            if (!($result instanceof RedirectResult)) {
                $crumbs = $this->updateCrumbs($__action, $result, $request, $reader);
                $fields = $this->assembleFields($action, $reader);

                $fields['headElements'] = HeadElements::filter(array_merge(
                    $fields['headElements'],
                    $executor->getHeadElements()));
            }
        } catch (\Exception $e) {
            $result = new FailedResult($e);
        }

        $resultModel = $this->assembleResult($result, $request);
        return array_merge(
            [
                'menu' => $this->app->menu->render($request),
                'breadcrumbs' => $crumbs ? array_slice($crumbs, 0, -1) : null,
                'current' => $crumbs ? array_slice($crumbs, -1)[0]['target'] : null,
                'action' => $caption,
                'description' => $description
            ],
            $resultModel,
            $fields
        );
    }

    private function assembleResult(ExecutionResult $result, WebRequest $request) {
        $model = [
            'error' => null,
            'missing' => null,
            'success' => null,
            'redirect' => null,
            'output' => null
        ];

        if ($result instanceof FailedResult) {
            $model['error'] = htmlentities($result->getMessage());
        } else if ($result instanceof NoResult) {
            $model['success'] = true;
            $model['redirect'] = $this->getLastCrumb();
        } else if ($result instanceof RenderedResult) {
            $model['output'] = $result->getOutput();
        } else if ($result instanceof MissingParametersResult) {
            $model['missing'] = $result->getParameters();
        } else if ($result instanceof RedirectResult) {
            $model['success'] = true;
            $model['redirect'] = $request->getContext()
                ->appended($result->getActionId())
                ->withParameters(new Map($result->getParameters()));
        }

        return $model;
    }

    private function assembleFields(Action $action, ParameterReader $reader) {
        $headElements = self::baseHeadElements();
        $fields = [];

        $values = $this->collectParameters($action, $reader);

        foreach ($action->parameters() as $parameter) {
            $field = $this->app->fields->getField($parameter);

            if (!($field instanceof WebField)) {
                throw new \Exception("[$parameter] is not a WebField");
            }

            $headElements = array_merge($headElements, $field->headElements($parameter));

            $fields[] = [
                'name' => $parameter->getName(),
                'description' => $parameter->getDescription(),
                'caption' => ucfirst($parameter->getName()),
                'required' => $parameter->isRequired(),
                'control' => $field->render($parameter, $values[$parameter->getName()]),
            ];
        }
        return [
            'headElements' => $headElements,
            'fields' => $fields
        ];
    }

    private function collectParameters(Action $action, ParameterReader $reader) {
        return $action->fill($this->readParameters($action, $reader));
    }

    private function readParameters(Action $action, ParameterReader $reader) {
        $values = [];

        foreach ($action->parameters() as $parameter) {
            if ($reader->has($parameter)) {
                $field = $this->app->fields->getField($parameter);
                $values[$parameter->getName()] = $field->inflate($parameter, $reader->read($parameter));
            }
        }
        return $values;
    }

    private function updateCrumbs($actionId, ExecutionResult $result, WebRequest $request, ParameterReader $reader) {
        $action = $this->app->actions->getAction($actionId);
        $crumbs = $this->readCrumbs();

        $current = [
            'target' => (string)$request->getContext()
                ->appended($actionId)
                ->withParameters(new Map($this->readRawParameters($action, $reader))),
            'caption' => $action->caption()
        ];
        $newCrumbs = [];
        foreach ($crumbs as $crumb) {
            if ($crumb == $current) {
                break;
            }
            $newCrumbs[] = $crumb;
        }
        $newCrumbs[] = $current;
        if ($result instanceof RenderedResult) {
            $this->saveCrumbs($newCrumbs);
        }
        return $newCrumbs;
    }

    private function readRawParameters(Action $action, ParameterReader $reader) {
        $values = [];

        foreach ($action->parameters() as $parameter) {
            if ($reader->has($parameter)) {
                $values[$parameter->getName()] = $reader->read($parameter);
            }
        }
        return $values;
    }

    private function getLastCrumb() {
        $crumbs = $this->readCrumbs();
        if (!$crumbs) {
            return null;
        }
        return end($crumbs)['target'];
    }

    private function readCrumbs() {
        if ($this->cookies->hasKey(self::BREADCRUMB_COOKIE)) {
            return $this->cookies->read(self::BREADCRUMB_COOKIE)->payload;
        }
        return [];
    }

    private function saveCrumbs($crumbs) {
        $this->cookies->create(new Cookie($crumbs), self::BREADCRUMB_COOKIE);
    }

    protected function createDefaultRenderer() {
        return new PhpRenderer();
    }
}