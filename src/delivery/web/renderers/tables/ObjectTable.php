<?php
namespace rtens\domin\delivery\web\renderers\tables;

use rtens\domin\reflection\types\TypeFactory;
use watoki\collections\Map;
use watoki\collections\Set;
use watoki\reflect\Property;
use watoki\reflect\PropertyReader;

class ObjectTable {

    /** @var object[] */
    private $objects;

    /** @var Map|Property[] */
    private $properties;

    /** @var array|string[] */
    private $headers = [];

    /** @var callable[] */
    private $filters = [];

    public function __construct(array $objects, TypeFactory $types) {
        $this->objects = $objects;
        $this->properties = new Map();

        if ($objects) {
            $reader = new PropertyReader($types, get_class($objects[0]));
            $this->properties = $reader->readInterface($objects[0])->filter(function (Property $property) {
                return $property->canGet();
            });
        }
    }

    /**
     * @return string[] Header captions
     */
    public function getHeaders() {
        $headers = [];
        foreach ($this->properties as $property) {
            if (array_key_exists($property->name(), $this->headers)) {
                $headers[] = $this->headers[$property->name()];
            } else {
                $headers[] = ucfirst($property->name());
            }
        }
        return $headers;
    }

    /**
     * @param object $object
     * @return mixed[]
     */
    public function getCells($object) {
        $row = [];
        foreach ($this->properties as $property) {
            $value = $property->get($object);
            if (array_key_exists($property->name(), $this->filters)) {
                $value = call_user_func($this->filters[$property->name()], $value);
            }
            $row[] = $value;
        }
        return $row;
    }

    public function selectProperties($names) {
        $this->properties = $this->properties->select(new Set($names));
        return $this;
    }

    public function setHeader($propertyName, $header) {
        $this->headers[$propertyName] = $header;
        return $this;
    }

    public function setFilter($propertyName, callable $filter) {
        $this->filters[$propertyName] = $filter;
        return $this;
    }

    /**
     * @return object[]
     */
    public function getObjects() {
        return $this->objects;
    }
}