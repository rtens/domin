<?php
namespace rtens\domin\cli\fields;

use rtens\domin\cli\CliField;
use rtens\domin\cli\Console;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use watoki\reflect\type\MultiType;

class MultiField implements CliField {

    /** @var FieldRegistry */
    private $fields;

    /** @var Console */
    private $console;

    /**
     * @param FieldRegistry $fields
     * @param Console $console
     */
    public function __construct(FieldRegistry $fields, Console $console) {
        $this->fields = $fields;
        $this->console = $console;
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() instanceof MultiType;
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        $type = $this->getTypes($parameter)[(int)$serialized];

        $optionParameter = new Parameter($parameter->getName(), $type);
        return $this->getField($optionParameter)->inflate($optionParameter, $this->input($optionParameter));
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
        $out = [''];
        foreach ($this->getTypes($parameter) as $i => $option) {
            $out[] = '  ' . $i . ' - ' . $option;
        }
        $out[] = 'Type';
        return implode(PHP_EOL, $out);
    }

    /**
     * @param Parameter $optionParameter
     * @return CliField
     */
    private function getField($optionParameter) {
        return $this->fields->getField($optionParameter);
    }

    private function getTypes(Parameter $parameter) {
        $type = $parameter->getType();
        if (!($type instanceof MultiType)) {
            throw new \InvalidArgumentException("[$type] must be a MultiType");
        }

        return $type->getTypes();
    }

    private function input(Parameter $parameter) {
        $prompt = $parameter->getName();

        $field = $this->getField($parameter);
        $description = $field->getDescription($parameter);
        if ($description) {
            $prompt .= ' ' . $description;
        }

        return $this->console->read($prompt . ':');
    }
}