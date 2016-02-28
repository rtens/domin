<?php
namespace rtens\domin\delivery\cli\fields;

use rtens\domin\delivery\cli\CliField;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;
use watoki\factory\Factory;
use watoki\factory\Injector;
use watoki\reflect\PropertyReader;
use watoki\reflect\type\ClassType;
use watoki\reflect\TypeFactory;

class ObjectField implements CliField {

    /** @var TypeFactory */
    private $types;

    /** @var FieldRegistry */
    private $fields;

    /** @var ParameterReader */
    private $reader;

    public function __construct(TypeFactory $types, FieldRegistry $fields, ParameterReader $reader) {
        $this->types = $types;
        $this->fields = $fields;
        $this->reader = $reader;
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() instanceof ClassType;
    }

    /**
     * @param Parameter $parameter
     * @param null $serialized
     * @return object
     */
    public function inflate(Parameter $parameter, $serialized) {
        $properties = [];
        foreach ($this->getProperties($parameter) as $property) {
            $propertyParameter = new Parameter($parameter->getName() . '-' . $property->name(), $property->type());

            $field = $this->getField($propertyParameter);
            $properties[$property->name()] = $field->inflate($propertyParameter, $this->reader->read($propertyParameter));
        }

        $injector = new Injector(new Factory());
        $instance = $injector->injectConstructor($this->getClass($parameter), $properties, function () {
            return false;
        });

        foreach ($this->getProperties($parameter) as $property) {
            $value = $properties[$property->name()];
            if (!is_null($value) && $property->canSet()) {
                $property->set($instance, $value);
            }
        }

        return $instance;
    }

    private function getClass(Parameter $parameter) {
        $type = $parameter->getType();
        if (!($type instanceof ClassType)) {
            throw new \InvalidArgumentException("[$type] is not a ClassType");
        }
        return $type->getClass();
    }

    /**
     * @param Parameter $param
     * @return CliField
     */
    private function getField(Parameter $param) {
        return $this->fields->getField($param);
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
        return '(press enter)';
    }

    private function getProperties(Parameter $parameter, $object = null) {
        $reader = new PropertyReader($this->types, $this->getClass($parameter));

        foreach ($reader->readInterface($object) as $property) {
            if ($property->canSet()) {
                yield $property;
            }
        }
    }
}