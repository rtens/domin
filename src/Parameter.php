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

    /** @var string */
    private $description = null;

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
     * @return string|null
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     * @return static
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
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

    public function __toString() {
        return $this->name . ':' . $this->type;
    }

    public function withType(Type $type) {
        return (new Parameter($this->getName(), $type, $this->isRequired()))
            ->setDescription($this->getDescription());
    }
}