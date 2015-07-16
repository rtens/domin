<?php
namespace rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\WebField;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\DoubleType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\LongType;
use watoki\reflect\type\PrimitiveType;

class PrimitiveField implements WebField {

    /**
     * @param \rtens\domin\Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() instanceof PrimitiveType && !($parameter->getType() instanceof BooleanType);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        switch (get_class($parameter->getType())) {
            case LongType::class:
            case IntegerType::class:
                return (int)$serialized;
            case DoubleType::class:
                return (double)$serialized;
            case FloatType::class:
                return (float)$serialized;
            default:
                return $serialized;
        }
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
            "class" => "form-control",
            "type" => $this->getType($parameter),
            "name" => $parameter->getName(),
            "value" => $value
        ];

        if ($parameter->isRequired()) {
            $attributes["required"] = 'required';
        }

        return (string)new Element("input", $attributes);
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    private function getType(Parameter $parameter) {
        switch (get_class($parameter->getType())) {
            case IntegerType::class:
                return 'number';
            default:
                return 'text';
        }
    }
}