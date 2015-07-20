<?php
namespace rtens\domin\cli\fields;

use rtens\domin\cli\CliField;
use rtens\domin\cli\Console;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use watoki\reflect\type\NullableType;

class NullableField implements CliField {

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
        return $parameter->getType() instanceof NullableType;
    }

    /**
     * @param Parameter $parameter
     * @param mixed $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        if (!$serialized || strtolower($serialized) == 'n') {
            return null;
        }

        $innerParameter = $this->getInnerParameter($parameter);
        $field = $this->getField($innerParameter);

        $prompt = $parameter->getName();
        $description = $field->getDescription($parameter);
        if ($description) {
            $prompt .= ' ' . $description;
        }

        return $field->inflate($innerParameter, $this->console->read($prompt . ':'));
    }

    private function getInnerParameter(Parameter $parameter) {
        $type = $parameter->getType();
        if (!($type instanceof NullableType)) {
            throw new \InvalidArgumentException("[$type] is not a NullableType");
        }

        return new Parameter($parameter->getName(), $type->getType());
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