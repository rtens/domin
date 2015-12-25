<?php
namespace rtens\domin;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\execution\ExecutionResult;
use rtens\domin\execution\FailedResult;
use rtens\domin\execution\MissingParametersResult;
use rtens\domin\execution\NoResult;
use rtens\domin\execution\NotPermittedResult;
use rtens\domin\execution\ValueResult;

class Executor {

    /** @var ActionRegistry */
    private $actions;

    /** @var FieldRegistry */
    private $fields;

    /** @var ParameterReader */
    private $paramReader;

    /** @var null|AccessControl */
    private $access;

    /**
     * @param ActionRegistry $actions <-
     * @param FieldRegistry $fields <-
     * @param ParameterReader $reader <-
     */
    public function __construct(ActionRegistry $actions, FieldRegistry $fields, ParameterReader $reader) {
        $this->actions = $actions;
        $this->fields = $fields;
        $this->paramReader = $reader;
    }

    public function restrictAccess(AccessControl $access) {
        $this->access = $access;
    }

    /**
     * @param $id
     * @return ExecutionResult
     */
    public function execute($id) {
        try {
            $action = $this->actions->getAction($id);

            list($params, $missing) = $this->readParameters($action);

            if (!empty($missing)) {
                return new MissingParametersResult($missing);
            }

            if ($this->access && !$this->access->isExecutionPermitted($id, $params)) {
                return new NotPermittedResult();
            }

            $returned = $action->execute($params);

            if (is_null($returned)) {
                return new NoResult();
            } else if ($returned instanceof ExecutionResult) {
                return $returned;
            } else {
                return new ValueResult($returned);
            }
        } catch (\Exception $e) {
            return new FailedResult($e);
        }
    }

    private function readParameters(Action $action) {
        $params = [];
        $missing = [];
        foreach ($action->parameters() as $parameter) {
            if ($this->paramReader->has($parameter)) {
                $inflated = $this->fields->getField($parameter)
                    ->inflate($parameter, $this->paramReader->read($parameter));

                if ($parameter->getType()->is($inflated)) {
                    $params[$parameter->getName()] = $inflated;
                } else if ($parameter->isRequired()) {
                    $missing[] = $parameter->getName();
                }
            } else if ($parameter->isRequired()) {
                $missing[] = $parameter->getName();
            }
        }
        return [$params, $missing];
    }
}