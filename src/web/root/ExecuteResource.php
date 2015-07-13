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
use rtens\domin\execution\RenderedResult;
use rtens\domin\Executor;
use rtens\domin\web\Element;
use rtens\domin\web\RequestParameterReader;
use rtens\domin\web\WebField;
use watoki\curir\delivery\WebRequest;
use watoki\curir\Resource;
use watoki\factory\Factory;

class ExecuteResource extends Resource {

    const ACTION_ARG = '__action';

    /** @var ActionRegistry */
    private $actions;

    /** @var FieldRegistry */
    private $fields;

    /** @var RendererRegistry */
    private $renderers;

    /**
     * @param Factory $factory <-
     * @param ActionRegistry $actions <-
     * @param FieldRegistry $fields <-
     * @param RendererRegistry $renderers <-
     */
    function __construct(Factory $factory, ActionRegistry $actions, FieldRegistry $fields, RendererRegistry $renderers) {
        parent::__construct($factory);
        $this->actions = $actions;
        $this->fields = $fields;
        $this->renderers = $renderers;
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
        $reader = new RequestParameterReader($request);
        $action = $this->actions->getAction($__action);

        $executor = new Executor($this->actions, $this->fields, $this->renderers, $reader);
        $result = $executor->execute($__action);

        return array_merge(
            [
                'action' => $action->caption()
            ],
            $this->assembleResult($result),
            $this->assembleFields($action, $reader)
        );
    }

    private function assembleResult(ExecutionResult $result) {
        $model = [
            'error' => null,
            'success' => null,
            'output' => null
        ];

        if ($result instanceof FailedResult) {
            $model['error'] = htmlentities($result->getMessage());
        } else if ($result instanceof NoResult) {
            $model['success'] = true;
        } else if ($result instanceof RenderedResult) {
            $model['output'] = $result->getOutput();
        } else if ($result instanceof MissingParametersResult) {
            $model['error'] = "Missing parameters: " . implode(', ', $result->getParameters());
        }

        return $model;
    }

    private function assembleFields(Action $action, ParameterReader $reader) {
        $headElements = [];
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
        $values = [];

        foreach ($action->parameters() as $parameter) {
            $value = $reader->read($parameter->getName());

            if (!is_null($value)) {
                $field = $this->fields->getField($parameter);
                $values[$parameter->getName()] = $field->inflate($value);
            }
        }

        return $action->fill($values);
    }
}