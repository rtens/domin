<?php
namespace rtens\domin\cli\fields;

use rtens\domin\cli\CliField;
use rtens\domin\Parameter;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\DoubleType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\LongType;
use watoki\reflect\type\PrimitiveType;

class PrimitiveField implements CliField {

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
                return htmlentities($serialized);
        }
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
    }
}