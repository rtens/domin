<?php
namespace rtens\domin\delivery\cli\fields;

use rtens\domin\delivery\cli\CliField;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\reflection\IdentifierType;

class IdentifierField implements CliField {

    /** @var FieldRegistry */
    private $fields;

    /**
     * @param FieldRegistry $fields
     */
    public function __construct(FieldRegistry $fields) {
        $this->fields = $fields;
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() instanceof IdentifierType;
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        $primitiveParameter = new Parameter($parameter->getName(), $this->getType($parameter)->getPrimitive());
        return $this->fields->getField($primitiveParameter)->inflate($primitiveParameter, $serialized);
    }

    /**
     * @param Parameter $parameter
     * @return IdentifierType
     */
    private function getType(Parameter $parameter) {
        $type = $parameter->getType();
        if (!($type instanceof IdentifierType)) {
            throw new \InvalidArgumentException("[$type] must be an IdentifierType");
        }
        return $type;
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
    }
}