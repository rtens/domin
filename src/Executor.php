<?php
namespace rtens\domin;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\execution\FailedResult;
use rtens\domin\execution\MissingParametersResult;
use rtens\domin\execution\NoResult;
use rtens\domin\execution\RenderedResult;

class Executor {

    /** @var ActionRegistry */
    private $actions;

    /** @var RendererRegistry */
    private $renderers;

    /** @var FieldRegistry */
    private $fields;

    /** @var ParameterReader */
    private $paramReader;

    /**
     * @param ActionRegistry $actions
     * @param FieldRegistry $fields
     * @param RendererRegistry $renderers
     * @param ParameterReader $reader
     */
    public function __construct(ActionRegistry $actions, FieldRegistry $fields, RendererRegistry $renderers, ParameterReader $reader) {
        $this->actions = $actions;
        $this->fields = $fields;
        $this->renderers = $renderers;
        $this->paramReader = $reader;
    }

    /**
     * @param $id
     * @return \rtens\domin\execution\ExecutionResult
     */
    public function execute($id) {
        try {
            $action = $this->actions->getAction($id);

            list($params, $missing) = $this->readParameters($action);

            if ($missing) {
                return new MissingParametersResult($missing);
            }

            $returned = $action->execute($params);

            if (is_null($returned)) {
                return new NoResult();
            }

            return new RenderedResult($this->render($returned));
        } catch (\Exception $e) {
            return new FailedResult($e);
        }
    }

    private function readParameters(Action $action) {
        $params = [];
        $missing = [];
        foreach ($action->parameters() as $name => $type) {
            $serialized = $this->paramReader->read($name);
            if (!is_null($serialized)) {
                $params[$name] = $this->fields->getField($type)->inflate($serialized);
            } else if ($action->isRequired($name)) {
                $missing[] = $name;
            }
        }
        return [$params, $missing];
    }

    private function render($returned) {
        return $this->renderers->getRenderer($returned)->render($returned);
    }
}