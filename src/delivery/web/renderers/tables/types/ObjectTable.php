<?php
namespace rtens\domin\delivery\web\renderers\tables\types;

use rtens\domin\delivery\web\renderers\tables\Table;
use rtens\domin\reflection\types\TypeFactory;
use watoki\reflect\Property;
use watoki\reflect\PropertyReader;

class ObjectTable implements Table {

    /** @var object[] */
    private $objects;

    /** @var Property[] */
    private $properties;

    /** @var string[] */
    private $headers = [];

    /** @var callable[] */
    private $filters = [];

    public function __construct(array $objects, TypeFactory $types) {
        $this->objects = $objects;
        $this->properties = [];

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
     * @return object[]
     */
    public function getItems() {
        return $this->objects;
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
        $selected = [];
        foreach ($names as $name) {
            $selected[$name] = $this->properties[$name];
        }
        $this->properties = $selected;
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
}