<?php
namespace rtens\domin\delivery\cli\fields;

use rtens\domin\Parameter;
use rtens\domin\reflection\types\RangeType;

class RangeField extends PrimitiveField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() instanceof RangeType;
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     * @throws \Exception
     */
    public function inflate(Parameter $parameter, $serialized) {
        if (!$parameter->getType()->is(intval($serialized))) {
            throw new \Exception("[$serialized] is not in range " . $parameter->getType());
        }

        return (int)$serialized;
    }
}