<?php
namespace rtens\domin\reflection;

use watoki\reflect\Type;

class EnumerationType implements Type {

    private $options;

    private $optionType;

    /**
     * @param array $options
     * @param Type $optionType
     */
    public function __construct(array $options, Type $optionType) {
        $this->options = $options;
        $this->optionType = $optionType;
    }

    public function getOptions() {
        return $this->options;
    }

    public function is($value) {
        return $value == $this->getOptions();
    }

    public function __toString() {
        return $this->optionType . '|enumeration';
    }

    /**
     * @return Type
     */
    public function getOptionType() {
        return $this->optionType;
    }
}