<?php
namespace rtens\domin\delivery;

use rtens\domin\Parameter;

interface ParameterReader {

    /**
     * IMPORTANT: files must be properly merged into the parameters
     *
     * @param Parameter $parameter
     * @return mixed The serialized paramater
     */
    public function read(Parameter $parameter);

    /**
     * @param Parameter $parameter
     * @return boolean
     */
    public function has(Parameter $parameter);
}