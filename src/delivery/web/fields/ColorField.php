<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebField;
use rtens\domin\Parameter;
use rtens\domin\parameters\Color;
use watoki\reflect\type\ClassType;

class ColorField implements WebField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(Color::class);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        return $serialized ? Color::fromHex($serialized) : null;
    }

    /**
     * @param Parameter $parameter
     * @param Color $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return (string)new Element('input', array_merge([
            'type' => 'color',
            'name' => $parameter->getName(),
            'value' => $value ? $value->asHex() : ''
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