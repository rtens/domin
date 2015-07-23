<?php
namespace rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\WebField;
use watoki\reflect\type\StringType;

class StringField implements WebField {

    /**
     * @param \rtens\domin\Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() instanceof StringType;
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        return (string)$serialized;
    }

    public function render(Parameter $parameter, $value) {
        return (string)new Element('input', array_merge([
            'class' => 'form-control',
            'type' => 'text',
            'name' => $parameter->getName(),
            'value' => $value
        ], $parameter->isRequired() ? [
            'required' => 'required'
        ] : []));
    }

    /**
     * @param Parameter $parameter
     * @return array|\rtens\domin\web\Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }
}