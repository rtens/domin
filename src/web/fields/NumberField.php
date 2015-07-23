<?php
namespace rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\WebField;
use watoki\reflect\type\DoubleType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\LongType;

class NumberField implements WebField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return in_array(get_class($parameter->getType()), [
            IntegerType::class,
            FloatType::class,
            LongType::class,
            DoubleType::class
        ]);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return int|float|double
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
                throw new \InvalidArgumentException("Not a number type [{$parameter->getType()}]");
        }
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return (string)new Element("input", [
            "class" => "form-control",
            "type" => $this->getType($parameter),
            "name" => $parameter->getName(),
            "value" => $value
        ]);
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

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }
}