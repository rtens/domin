<?php
namespace rtens\domin\delivery\cli;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;

class InteractiveCliParameterReader implements ParameterReader {

    private $fields;
    private $console;

    /**
     * @param FieldRegistry $fields <-
     * @param Console $console
     */
    public function __construct(FieldRegistry $fields, Console $console) {
        $this->fields = $fields;
        $this->console = $console;
    }

    /**
     * @param Parameter $parameter
     * @return boolean
     */
    public function has(Parameter $parameter) {
        return true;
    }

    /**
     * @param Parameter $parameter
     * @return string
     * @throws \Exception
     */
    public function read(Parameter $parameter) {
        $prompt = $parameter->getName();
        if ($parameter->isRequired()) {
            $prompt = $prompt . '*';
        }

        $field = $this->getField($parameter);
        $description = $field->getDescription($parameter);
        if ($description !== null) {
            $prompt .= ' ' . $description;
        }

        $value = $this->console->read($prompt . ': ');
        while ($parameter->isRequired() && !$value) {
            $value = $this->console->read($prompt . ': ');
        }
        return $value;
    }

    /**
     * @param Parameter $parameter
     * @return \rtens\domin\delivery\cli\CliField
     * @throws \Exception
     */
    private function getField(Parameter $parameter) {
        $field = $this->fields->getField($parameter);
        if (!($field instanceof CliField)) {
            $fieldClass = get_class($field);
            throw new \Exception("Not a CliField [$fieldClass]");
        }
        return $field;
    }
}