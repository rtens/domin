<?php
namespace rtens\domin\web\root;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\execution\ExecutionResult;
use rtens\domin\execution\FailedResult;
use rtens\domin\execution\MissingParametersResult;
use rtens\domin\execution\NoResult;
use rtens\domin\execution\RedirectResult;
use rtens\domin\execution\RenderedResult;
use rtens\domin\Executor;
use rtens\domin\web\Element;
use rtens\domin\web\HeadElements;
use rtens\domin\web\menu\Menu;
use rtens\domin\web\RequestParameterReader;
use rtens\domin\web\WebField;
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

    /** @var ActionRegistry */
    private $actions;

    /** @var FieldRegistry */
    private $fields;

    /** @var RendererRegistry */
    private $renderers;

    /** @var Menu */
    private $menu;

    /** @var CookieStore */
    private $cookies;

    /**
     * @param Factory $factory <-
     * @param ActionRegistry $actions <-
     * @param FieldRegistry $fields <-
     * @param RendererRegistry $renderers <-
     * @param Menu $menu <-
     * @param CookieStore $cookies <-
     */
    function __construct(Factory $factory, ActionRegistry $actions, FieldRegistry $fields,
                         RendererRegistry $renderers, Menu $menu, CookieStore $cookies) {
        parent::__construct($factory);
        $this->actions = $actions;
        $this->fields = $fields;
        $this->renderers = $renderers;
        $this->menu = $menu;
        $this->cookies = $cookies;
    }

    private static function baseHeadElements() {
        return [
            (string)HeadElements::jquery(),
            (string)HeadElements::bootstrap(),
            (string)HeadElements::bootstrapJs(),
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
        $crumbs = [];

        $reader = new RequestParameterReader($request);

        try {
            $action = $this->actions->getAction($__action);
            $caption = $action->caption();

            $fields = $this->assembleFields($action, $reader);

            $executor = new Executor($this->actions, $this->fields, $this->renderers, $reader);
            $result = $executor->execute($__action);

            $crumbs = $this->updateCrumbs($__action, $result, $request, $reader);
        } catch (\Exception $e) {
            $result = new FailedResult($e);
        }

        $resultModel = $this->assembleResult($result, $request);
        return array_merge(
            [
                'menuItems' => $this->menu->assembleModel($request),
                'breadcrumbs' => $crumbs ? array_slice($crumbs, 0, -1) : null,
                'action' => $caption,
                'baseUrl' => $request->getContext()->appended('')->toString()
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
            $field = $this->fields->getField($parameter);

            if (!($field instanceof WebField)) {
                throw new \Exception("[$parameter] is not a WebField");
            }

            $headElements = array_merge($headElements, array_map(function (Element $element) {
                return (string)$element;
            }, $field->headElements($parameter)));

            $fields[] = [
                'name' => $parameter->getName(),
                'caption' => ucfirst($parameter->getName()),
                'required' => $parameter->isRequired(),
                'control' => $field->render($parameter, $values[$parameter->getName()]),
            ];
        }
        return [
            'headElements' => array_values(array_unique($headElements)),
            'fields' => $fields
        ];
    }

    private function collectParameters(Action $action, ParameterReader $reader) {
        return $action->fill($this->readParameters($action, $reader));
    }

    private function readParameters(Action $action, ParameterReader $reader) {
        $values = [];

        foreach ($action->parameters() as $parameter) {
            $value = $reader->read($parameter);

            if (!is_null($value)) {
                $field = $this->fields->getField($parameter);
                $values[$parameter->getName()] = $field->inflate($parameter, $value);
            }
        }
        return $values;
    }

    private function updateCrumbs($actionId, ExecutionResult $result, WebRequest $request, ParameterReader $reader) {
        $action = $this->actions->getAction($actionId);
        $crumbs = $this->readCrumbs();

        $current = [
            'target' => (string)$request->getContext()
                ->appended($actionId)
                ->withParameters(new Map($this->readParameters($action, $reader))),
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