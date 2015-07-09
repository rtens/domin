<?php
namespace rtens\domin;

class Parameter {

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var boolean */
    private $required;

    public function __construct($name, $type, $required = false) {
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
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    function __toString() {
        return $this->name . ':' . $this->type;
    }


} 