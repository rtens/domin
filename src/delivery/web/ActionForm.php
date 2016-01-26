<?php
namespace rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;
use watoki\reflect\type\ClassType;

class ActionForm {

    /** @var array */
    private $model;

    /** @var array|Element[] */
    private $headElements = [];

    public function __construct(ParameterReader $reader, FieldRegistry $fields, Action $action, $actionId) {
        $actionParameter = new Parameter($actionId, new ClassType(get_class($action)));
        $actionField = $this->getActionField($actionParameter, $fields);

        $this->model = $actionField->render($actionParameter, $this->readParameters($reader, $fields, $action));
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
     * @param FieldRegistry $fields
     * @return WebField
     * @throws \Exception
     */
    private function getActionField(Parameter $actionParameter, FieldRegistry $fields) {
        $actionField = $fields->getField($actionParameter);
        if ($actionField instanceof WebField) {
            return $actionField;
        }
        throw new \Exception(get_class($actionField) . " must implement WebField");
    }

    private function readParameters(ParameterReader $reader, FieldRegistry $fields, Action $action) {
        $values = [
            'inflated' => [],
            'errors' => []
        ];

        foreach ($action->parameters() as $parameter) {
            if ($reader->has($parameter)) {
                $field = $fields->getField($parameter);

                try {
                    $values['inflated'][$parameter->getName()] = $field->inflate($parameter, $reader->read($parameter));
                } catch (\Exception $e) {
                    $values['errors'][$parameter->getName()] = $e;
                }
            }
        }
        return $values;
    }
}