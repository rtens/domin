<?php
namespace rtens\domin;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
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
        $failed = [];
        $params = [];
        foreach ($action->parameters() as $parameter) {
            try {
                $params[$parameter->getName()] = $this->inflate($parameter);
            } catch (\Exception $e) {
                $failed[$parameter->getName()] = $e;
            }
        }

        return [$params, $failed];
    }

    private function inflate(Parameter $parameter) {
        if ($this->paramReader->has($parameter)) {
            $inflated = $this->fields->getField($parameter)
                ->inflate($parameter, $this->paramReader->read($parameter));

            if ($parameter->getType()->is($inflated)) {
                return $inflated;
            }
        }

        if ($parameter->isRequired()) {
            throw new \Exception("[{$parameter->getName()}] is required.");
        }

        return null;
    }
}