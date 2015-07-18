<?php
namespace rtens\domin\cli\fields;

use rtens\domin\cli\CliField;
use rtens\domin\Parameter;
use rtens\domin\parameters\Html;
use watoki\reflect\type\ClassType;

class HtmlField implements CliField {

    private $input;

    function __construct(callable $input) {
        $this->input = $input;
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(Html::class);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return Html
     */
    public function inflate(Parameter $parameter, $serialized) {
        $line = $serialized;
        while ($line) {
            $line = $this->input();
            $serialized .= "\n" . $line;
        }
        return new Html($serialized);
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
    }

    private function input() {
        return call_user_func($this->input, '');
    }
}