<?php
namespace rtens\domin\reflection;

use rtens\domin\Action;
use rtens\domin\Parameter;
use watoki\reflect\PropertyReader;

abstract class ObjectAction implements Action {

    /** @var \ReflectionClass */
    protected $class;

    /**
     * @param string $class
     */
    public function __construct($class) {
        $this->class = new \ReflectionClass($class);
    }

    /**
     * Called by execute() with the instantiated object
     *
     * @param object $object
     * @return mixed
     */
    abstract protected function executeWith($object);

    /**
     * @return string
     */
    public function caption() {
        return preg_replace('/(.)([A-Z0-9])/', '$1 $2', $this->class->getShortName());
    }

    /**
     * @return Parameter[]
     */
    public function parameters() {
        $reader = new PropertyReader($this->class->getName());

        $parameters = [];
        foreach ($reader->readInterface() as $property) {
            if ($property->canSet()) {
                $parameters[] = new Parameter($property->name(), $property->type(), $property->isRequired());
            }
        }
        return $parameters;
    }

    /**
     * @param mixed[] $parameters Values indexed by name
     * @return mixed the result of the execution
     * @throws \Exception if Action cannot be executed
     */
    public function execute(array $parameters) {
        return $this->executeWith($this->createInstance($parameters));
    }

    protected function createInstance(array $parameters) {
        $reader = new PropertyReader($this->class->getName());
        $instance = $this->class->newInstanceArgs($parameters);
        foreach ($reader->readInterface() as $property) {
            if ($property->canSet() && array_key_exists($property->name(), $parameters)) {
                $property->set($instance, $parameters[$property->name()]);
            }
        }
        return $instance;
    }
}