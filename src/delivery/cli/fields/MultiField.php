<?php
namespace rtens\domin\delivery\cli\fields;

use rtens\domin\delivery\cli\CliField;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;
use watoki\reflect\type\MultiType;

class MultiField implements CliField {

    /** @var FieldRegistry */
    private $fields;

    /** @var ParameterReader */
    private $reader;

    public function __construct(FieldRegistry $fields, ParameterReader $reader) {
        $this->fields = $fields;
        $this->reader = $reader;
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

        $optionParameter = new Parameter($parameter->getName() . '-value', $type);
        return $this->getField($optionParameter)->inflate($optionParameter, $this->reader->read($optionParameter));
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
}