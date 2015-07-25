<?php
namespace rtens\domin\reflection;

use rtens\domin\Action;
use rtens\domin\Parameter;
use rtens\domin\reflection\types\TypeFactory;
use watoki\factory\Factory;
use watoki\factory\Injector;
use watoki\reflect\PropertyReader;

abstract class ObjectAction implements Action {

    /** @var PropertyReader */
    private $reader;

    /** @var \ReflectionClass */
    protected $class;

    /**
     * @param string $class
     * @param TypeFactory $types
     */
    public function __construct($class, TypeFactory $types) {
        $this->reader = new PropertyReader($types, $class);
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
     * @return string|null
     */
    public function description() {
        $docComment = $this->class->getDocComment();
        if (!$docComment) {
            return null;
        }

        return implode("\n", array_map(function ($line) {
            return ltrim($line, " *\r\n\t");
        }, array_slice(explode("\n", $docComment), 1, -1)));
    }

    /**
     * Fills out partially available parameters
     *
     * @param array $parameters Available values indexed by name
     * @return array Filled values indexed by name
     */
    public function fill(array $parameters) {
        foreach ($this->reader->readInterface() as $property) {
            if (!array_key_exists($property->name(), $parameters)) {
                $parameters[$property->name()] = $property->defaultValue();
            }
        }
        return $parameters;
    }

    /**
     * @return Parameter[]
     * @throws \Exception
     */
    public function parameters() {
        $parameters = [];
        foreach ($this->reader->readInterface() as $property) {
            if ($property->canSet()) {
                $parameters[] = (new Parameter($property->name(), $property->type(), $property->isRequired()))
                    ->setDescription($property->comment());
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
        $injector = new Injector(new Factory());
        $instance = $injector->injectConstructor($this->class->name, $parameters, function () {
            return false;
        });

        foreach ($this->reader->readInterface() as $property) {
            if ($property->canSet() && array_key_exists($property->name(), $parameters)) {
                $property->set($instance, $parameters[$property->name()]);
            }
        }
        return $instance;
    }
}