<?php
namespace spec\rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\WebField;
use watoki\reflect\type\IntegerType;

class NumberField implements WebField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new IntegerType();
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        return (int)$serialized;
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return (string)new Element('input', array_merge([
            'class' => 'form-control',
            'type' => 'number',
            'value' => $value
        ], $parameter->isRequired() ? [
            'required' => 'required'
        ] : []));
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }
}