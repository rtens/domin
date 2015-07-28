<?php
namespace rtens\domin\delivery\web\renderers\table;

use rtens\domin\reflection\types\TypeFactory;
use watoki\reflect\Property;
use watoki\reflect\PropertyReader;

class DefaultTableConfiguration implements TableConfiguration {

    const COLUMN_COUNT = 5;

    /** @var TypeFactory */
    protected $types;

    /**
     * @param TypeFactory $types
     */
    public function __construct(TypeFactory $types) {
        $this->types = $types;
    }

    /**
     * @param object $object
     * @return boolean
     */
    public function appliesTo($object) {
        return true;
    }

    /**
     * @param $object
     * @return Property[]
     */
    public function getProperties($object) {
        $reader = new PropertyReader($this->types, get_class($object));

        $properties = [];
        foreach ($reader->readInterface($object) as $property) {
            if ($property->canGet()) {
                $properties[] = $property;
                if (count($properties) == self::COLUMN_COUNT) {
                    break;
                }
            }
        }
        return $properties;
    }

    /**
     * @param Property $property
     * @return string
     */
    public function getHeaderCaption(Property $property) {
        return ucfirst($property->name());
    }

    /**
     * @param Property $property
     * @param object $object
     * @return mixed
     */
    public function getValue(Property $property, $object) {
        return $property->get($object);
    }
}