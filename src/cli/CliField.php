<?php
namespace rtens\domin\cli;

use rtens\domin\delivery\Field;
use rtens\domin\Parameter;

interface CliField extends Field {

    /**
     * @param Parameter $parameter
     * @return null|string
     */
    public function getDescription(Parameter $parameter);
}