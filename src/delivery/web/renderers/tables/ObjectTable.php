<?php
namespace rtens\domin\delivery\web\renderers\tables;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\reflection\types\TypeFactory;
use watoki\collections\Map;
use watoki\collections\Set;
use watoki\reflect\Property;
use watoki\reflect\PropertyReader;

class ObjectTable implements Table {

    /** @var object[] */
    private $objects;

    /** @var Map|Property[] */
    private $properties = [];

    /** @var array|string[] */
    private $headers = [];

    /** @var callable[] */
    private $filters = [];

    public function __construct(array $objects, TypeFactory $types) {
        $this->objects = $objects;

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
        $headers = [''];
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
     * @param null|LinkPrinter $linkPrinter
     * @return \mixed[][] Rows containing the Element of each cell
     */
    public function getRows(LinkPrinter $linkPrinter = null) {
        $rows = [];
        foreach ($this->objects as $object) {
            $row = [];
            if ($linkPrinter) {
                $row[] = new Element('div', [], $linkPrinter->createDropDown($object));
            } else {
                $row[] = '';
            }

            foreach ($this->properties as $property) {
                $value = $property->get($object);
                if (array_key_exists($property->name(), $this->filters)) {
                    $value = call_user_func($this->filters[$property->name()], $value);
                }
                $row[] = $value;
            }
            $rows[] = $row;
        }
        return $rows;
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
}