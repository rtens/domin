<?php
namespace rtens\domin\web;

use rtens\domin\delivery\Field;

interface WebField extends Field {

    public function render($name, $value, $isRequired = false);
}