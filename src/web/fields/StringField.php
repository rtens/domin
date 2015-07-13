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
     * @param string $serialized
     * @return mixed
     */
    public function inflate($serialized) {
        return $serialized ?: null;
    }

    /**
     * @param Parameter $parameter
     * @return array|\rtens\domin\web\Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }

    public function render(Parameter $parameter, $value) {
        $attributes = [
            "type" => "text",
            "name" => $parameter->getName(),
            "value" => $value
        ];

        if ($parameter->isRequired()) {
            $attributes["required"] = 'required';
        }

        return (string) new Element("input", $attributes);
    }
}