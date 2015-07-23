<?php
namespace rtens\domin\delivery\cli\fields;

use rtens\domin\delivery\cli\CliField;
use rtens\domin\Parameter;
use watoki\reflect\type\BooleanType;

class BooleanField implements CliField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new BooleanType();
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        return strtolower($serialized != 'n') && $serialized;
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
        return '(y|N)';
    }
}