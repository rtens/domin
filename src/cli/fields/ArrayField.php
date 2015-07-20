<?php
namespace rtens\domin\cli\fields;

use rtens\domin\cli\CliField;
use rtens\domin\cli\Console;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use watoki\reflect\type\ArrayType;

class ArrayField implements CliField {

    /** @var FieldRegistry */
    private $fields;

    /** @var Console */
    private $console;

    public function __construct(FieldRegistry $fields, Console $console) {
        $this->fields = $fields;
        $this->console = $console;
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
        $itemParameter = $this->makeInnerParameter($parameter);

        $items = [];
        for ($i = 0; $i < $serialized; $i++) {
            $field = $this->getField($itemParameter);

            $prompt = $i;
            $description = $field->getDescription($itemParameter);
            if ($description !== null) {
                $prompt .= ' ' . $description;
            }

            $items[] = $field->inflate($itemParameter, $this->console->read($prompt . ':'));
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