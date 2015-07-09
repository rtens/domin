<?php
namespace rtens\domin\web;

use rtens\domin\delivery\Field;
use rtens\domin\Parameter;

interface WebField extends Field {

    public function render(Parameter $parameter, $value);
}