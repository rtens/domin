<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\parameters\IdentifiersProvider;
use rtens\domin\reflection\types\IdentifierType;

class IdentifierField extends EnumerationField {

    /** @var IdentifiersProvider */
    private $identifiers;

    /**
     * @param FieldRegistry $fields
     * @param IdentifiersProvider $identifiers
     */
    public function __construct(FieldRegistry $fields, IdentifiersProvider $identifiers) {
        parent::__construct($fields);
        $this->identifiers = $identifiers;
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
     * @return mixed
     * @throws \Exception
     */
    protected function getOptions(Parameter $parameter) {
        return $this->identifiers->getIdentifiers($this->getType($parameter)->getTarget());
    }

    /**
     * @param Parameter $parameter
     * @return IdentifierType
     */
    private function getType(Parameter $parameter) {
        return $parameter->getType();
    }

    /**
     * @param Parameter $parameter
     * @return \watoki\reflect\Type
     */
    protected function getOptionType(Parameter $parameter) {
        return $this->getType($parameter)->getPrimitive();
    }
}