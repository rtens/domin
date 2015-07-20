<?php
namespace rtens\domin\cli\fields;

use rtens\domin\cli\CliField;
use rtens\domin\cli\Console;
use rtens\domin\delivery\FieldRegistry;
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

    /** @var Console */
    private $console;

    /**
     * @param TypeFactory $types
     * @param FieldRegistry $fields
     * @param Console $console
     */
    public function __construct(TypeFactory $types, FieldRegistry $fields, Console $console) {
        $this->types = $types;
        $this->fields = $fields;
        $this->console = $console;
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
        $reader = new PropertyReader($this->types, $this->getClass($parameter));

        $properties = [];
        foreach ($reader->readInterface() as $property) {
            $param = new Parameter($property->name(), $property->type());
            $prompt = $property->name();
            if (!$property->isRequired()) {
                $prompt = '[' . $prompt . ']';
            }

            $field = $this->getField($param);

            $description = $field->getDescription($param);
            if ($description) {
                $prompt .= ' ' . $description;
            }

            $input = $this->console->read('  ' . $prompt . ':');
            $properties[$property->name()] = $field->inflate($param, $input);
        }

        $injector = new Injector(new Factory());
        $instance = $injector->injectConstructor($this->getClass($parameter), $properties, function () {
            return false;
        });

        foreach ($reader->readInterface() as $property) {
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
}