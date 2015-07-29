<?php
namespace rtens\domin\delivery\web\renderers\table;

use rtens\domin\reflection\types\TypeFactory;
use watoki\collections\Set;
use watoki\reflect\Property;
use watoki\reflect\PropertyReader;

class GenericTableConfiguration extends DefaultTableConfiguration {

    /** @var string */
    private $class;

    /** @var string[] */
    private $properties;

    /** @var callable[] */
    private $filters = [];

    /** @var string[] */
    private $captions = [];

    /**
     * @param TypeFactory $types
     * @param string $class
     * @param string[] $properties
     */
    public function __construct(TypeFactory $types, $class, $properties = []) {
        parent::__construct($types);
        $this->class = $class;
        $this->properties = $properties;
    }

    /**
     * @param object $object
     * @return boolean
     */
    public function appliesTo($object) {
        return is_a($object, $this->class);
    }

    /**
     * @param object $object
     * @return Property[]
     */
    public function getProperties($object) {
        if ($this->properties) {
            $reader = new PropertyReader($this->types, get_class($object));
            return $reader->readInterface($object)->select(new Set($this->properties));
        }
        return parent::getProperties($object);
    }

    /**
     * @param Property $property
     * @return string
     */
    public function getHeaderCaption(Property $property) {
        if (array_key_exists($property->name(), $this->captions)) {
            return $this->captions[$property->name()];
        }
        return parent::getHeaderCaption($property);
    }

    /**
     * @param string $propertyName
     * @param string $caption
     * @return $this
     */
    public function setHeaderCaption($propertyName, $caption) {
        $this->captions[$propertyName] = $caption;
        return $this;
    }

    /**
     * @param Property $property
     * @param object $object
     * @return mixed
     */
    public function getValue(Property $property, $object) {
        $value = parent::getValue($property, $object);
        if (array_key_exists($property->name(), $this->filters)) {
            return call_user_func($this->filters[$property->name()], $value);
        }
        return $value;
    }

    /**
     * @param $propertyName
     * @param callable $filter Receives the value to be filtered
     * @return $this
     */
    public function setFilter($propertyName, callable $filter) {
        $this->filters[$propertyName] = $filter;
        return $this;
    }
}