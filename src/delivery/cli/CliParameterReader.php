<?php
namespace rtens\domin\delivery\cli;

use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;

class CliParameterReader implements ParameterReader {

    /** @var Console */
    private $console;

    public function __construct(Console $console) {
        $this->console = $console;
    }

    /**
     * @param Parameter $parameter
     * @return mixed The serialized paramater
     */
    public function read(Parameter $parameter) {
        return $this->console->getOption($parameter->getName());
    }

    /**
     * @param Parameter $parameter
     * @return boolean
     */
    public function has(Parameter $parameter) {
        return $this->console->hasOption($parameter->getName());
    }
}