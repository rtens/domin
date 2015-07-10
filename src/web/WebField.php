<?php
namespace rtens\domin\web;

use rtens\domin\delivery\Field;
use rtens\domin\Parameter;

interface WebField extends Field {

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value);

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter);
}