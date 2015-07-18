<?php
namespace rtens\domin\delivery;

use rtens\domin\Parameter;

interface ParameterReader {

    /**
     * @param Parameter $parameter
     * @return null|string The serialized paramater
     */
    public function read(Parameter $parameter);
}