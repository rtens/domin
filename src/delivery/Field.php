<?php
namespace rtens\domin\delivery;

use rtens\domin\Parameter;

interface Field {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter);

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized);
}