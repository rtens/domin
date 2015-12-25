<?php
namespace rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\Parameter;
use watoki\curir\delivery\WebRequest;
use watoki\reflect\type\ClassType;

class ActionForm {

    /** @var array */
    private $model;

    /** @var array|Element[] */
    private $headElements = [];

    public function __construct(WebRequest $request, WebApplication $app, Action $action, $actionId) {
        $actionParameter = new Parameter($actionId, new ClassType(get_class($action)));
        $actionField = $this->getActionField($actionParameter, $app);

        $this->model = $actionField->render($actionParameter, $this->readParameters($request, $app, $action));
        $this->headElements = $actionField->headElements($actionParameter);
    }

    public function getModel() {
        return $this->model;
    }

    public function getHeadElements() {
        return $this->headElements;
    }

    /**
     * @param Parameter $actionParameter
     * @param WebApplication $app
     * @return WebField
     * @throws \Exception
     */
    private function getActionField(Parameter $actionParameter, WebApplication $app) {
        $actionField = $app->fields->getField($actionParameter);
        if ($actionField instanceof WebField) {
            return $actionField;
        }
        throw new \Exception(get_class($actionField) . " must implement WebField");
    }

    private function readParameters(WebRequest $request, WebApplication $app, Action $action) {
        $reader = new RequestParameterReader($request);
        $values = [];

        foreach ($action->parameters() as $parameter) {
            if ($reader->has($parameter)) {
                $field = $app->fields->getField($parameter);
                $values[$parameter->getName()] = $field->inflate($parameter, $reader->read($parameter));
            }
        }
        return $values;
    }
}