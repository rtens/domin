<?php
namespace rtens\domin;

use watoki\reflect\Type;

class Parameter {

    /** @var string */
    private $name;

    /** @var Type */
    private $type;

    /** @var boolean */
    private $required;

    public function __construct($name, Type $type, $required = false) {
        $this->name = $name;
        $this->required = $required;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isRequired() {
        return $this->required;
    }

    /**
     * @return Type
     */
    public function getType() {
        return $this->type;
    }

    function __toString() {
        return $this->name . ':' . $this->type;
    }
} 