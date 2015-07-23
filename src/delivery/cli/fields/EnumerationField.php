<?php
namespace rtens\domin\delivery\cli\fields;

use rtens\domin\delivery\cli\CliField;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\reflection\types\EnumerationType;

class EnumerationField implements CliField {

    /** @var FieldRegistry */
    private $fields;

    /**
     * EnumerationField constructor.
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
        return $parameter->getType() instanceof EnumerationType;
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        $param = new Parameter($parameter->getName(), $this->getType($parameter)->getOptionType());
        return $this->fields->getField($param)->inflate($param, $this->getType($parameter)->getOptions()[(int)$serialized]);
    }

    /**
     * @param Parameter $parameter
     * @return EnumerationType
     */
    private function getType(Parameter $parameter) {
        $type = $parameter->getType();
        if (!($type instanceof EnumerationType)) {
            throw new \InvalidArgumentException("[$type] must be an EnumerationType");
        }
        return $type;
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
        $out = [''];
        foreach ($this->getType($parameter)->getOptions() as $i => $option) {
            $out[] = '  ' . $i . ' - ' . $option;
        }
        $out[] = 'Selection';
        return implode(PHP_EOL, $out);
    }
}