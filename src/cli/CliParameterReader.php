<?php
namespace rtens\domin\cli;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;

class CliParameterReader implements ParameterReader {

    private $fields;
    private $console;

    /**
     * @param FieldRegistry $fields <-
     * @param Console $console
     */
    function __construct(FieldRegistry $fields, Console $console) {
        $this->fields = $fields;
        $this->console = $console;
    }

    public function read(Parameter $parameter) {
        $prompt = $parameter->getName();
        if ($parameter->isRequired()) {
            $prompt = $prompt . '*';
        }

        $field = $this->getField($parameter);
        $description = $field->getDescription($parameter);
        if ($description) {
            $prompt .= ' ' . $description;
        }

        $value = $this->console->read($prompt . ':');
        if ($parameter->isRequired()) {
            while (!$value) {
                $value = $this->console->read($prompt . ':');
            }
        }
        return $value;
    }

    /**
     * @param Parameter $parameter
     * @return \rtens\domin\cli\CliField
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