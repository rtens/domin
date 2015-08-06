<?php
namespace rtens\domin\delivery\cli\fields;

use rtens\domin\delivery\cli\CliField;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;
use watoki\reflect\type\NullableType;

class NullableField implements CliField {

    /** @var FieldRegistry */
    private $fields;

    /** @var ParameterReader */
    private $reader;

    /**
     * @param FieldRegistry $fields
     * @param ParameterReader $reader
     */
    public function __construct(FieldRegistry $fields, ParameterReader $reader) {
        $this->fields = $fields;
        $this->reader = $reader;
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() instanceof NullableType;
    }

    /**
     * @param Parameter $parameter
     * @param mixed $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        $innerParameter = $this->getInnerParameter($parameter);

        if (!$this->reader->has($innerParameter) || !$serialized || strtolower($serialized) == 'n') {
            return null;
        }

        $field = $this->getField($innerParameter);
        return $field->inflate($innerParameter, $this->reader->read($innerParameter));
    }

    private function getInnerParameter(Parameter $parameter) {
        $type = $parameter->getType();
        if (!($type instanceof NullableType)) {
            throw new \InvalidArgumentException("[$type] is not a NullableType");
        }

        return new Parameter($parameter->getName() . '-value', $type->getType());
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
        return '? (y|N)';
    }

    /**
     * @param $innerParameter
     * @return CliField
     * @throws \Exception
     */
    private function getField($innerParameter) {
        return $this->fields->getField($innerParameter);
    }
}