<?php
namespace rtens\domin\cli\fields;

use rtens\domin\cli\CliField;
use rtens\domin\Parameter;
use watoki\reflect\type\DoubleType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\LongType;
use watoki\reflect\type\StringType;

class PrimitiveField implements CliField {

    /**
     * @param \rtens\domin\Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return in_array(get_class($parameter->getType()), [
            StringType::class,
            IntegerType::class,
            FloatType::class,
            LongType::class,
            DoubleType::class
        ]);
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
                return (string)$serialized;
        }
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
    }
}