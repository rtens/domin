<?php
namespace rtens\domin\cli;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;

class CliParameterReader implements ParameterReader {

    private $fields;

    /**
     * @param FieldRegistry $fields <-
     * @param callable $read
     */
    function __construct(FieldRegistry $fields, callable $read) {
        $this->fields = $fields;
        $this->read = $read;
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

        $value = call_user_func($this->read, $prompt . ':');
        if ($parameter->isRequired()) {
            while (!$value) {
                $value = call_user_func($this->read, $prompt . ':');
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