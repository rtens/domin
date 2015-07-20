<?php
namespace rtens\domin\cli\fields;

use rtens\domin\cli\CliField;
use rtens\domin\cli\Console;
use rtens\domin\Parameter;
use rtens\domin\parameters\Html;
use watoki\reflect\type\ClassType;

class HtmlField implements CliField {

    /** @var Console */
    private $console;

    public function __construct(Console $console) {
        $this->console = $console;
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
            $line = $this->console->read();
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
}