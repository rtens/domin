<?php
namespace rtens\domin\delivery\cli\fields;

use rtens\domin\delivery\cli\CliField;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;
use watoki\reflect\type\ArrayType;

class ArrayField implements CliField {

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
        return $parameter->getType() instanceof ArrayType;
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return array
     */
    public function inflate(Parameter $parameter, $serialized) {

        $items = [];
        for ($i = 0; $i < $serialized; $i++) {
            $itemParameter = $this->makeInnerParameter($parameter, "-$i");
            $field = $this->getField($itemParameter);
            $items[] = $field->inflate($itemParameter, $this->reader->read($itemParameter));
        }
        return $items;
    }

    /**
     * @param Parameter $parameter
     * @param string $suffix
     * @return Parameter
     */
    private function makeInnerParameter(Parameter $parameter, $suffix = '') {
        /** @var ArrayType $type */
        $type = $parameter->getType();
        return new Parameter($parameter->getName() . $suffix, $type->getItemType());
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
        return '(size)';
    }

    /**
     * @param $itemParameter
     * @return CliField
     * @throws \Exception
     */
    private function getField($itemParameter) {
        return $this->fields->getField($itemParameter);
    }
}