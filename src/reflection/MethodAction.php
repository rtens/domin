<?php
namespace rtens\domin\reflection;

use rtens\domin\Action;
use rtens\domin\Parameter;
use rtens\domin\reflection\types\TypeFactory;
use watoki\reflect\MethodAnalyzer;

class MethodAction implements Action {

    /** @var object */
    private $object;

    /** @var \ReflectionMethod */
    private $method;

    /** @var TypeFactory */
    private $types;

    /** @var CommentParser */
    private $parser;

    /**
     * @param object $object
     * @param string $method
     * @param TypeFactory $types
     * @param CommentParser $parser
     */
    public function __construct($object, $method, TypeFactory $types, CommentParser $parser) {
        $this->object = $object;
        $this->method = new \ReflectionMethod(get_class($object), $method);
        $this->types = $types;
        $this->parser = $parser;
    }

    /**
     * @return string
     */
    public function caption() {
        return $this->unCamelize($this->method->getDeclaringClass()->getShortName()) . ': ' . $this->unCamelize($this->method->name);
    }

    private function unCamelize($camel) {
        return ucfirst(preg_replace('/(.)([A-Z0-9])/', '$1 $2', $camel));
    }

    /**
     * @return string|null
     */
    public function description() {
        $lines = array_slice(explode("\n", $this->method->getDocComment()), 1, -1);
        $lines = array_map(function ($line) {
            return ltrim($line, ' *');
        }, $lines);
        $lines = array_filter($lines, function ($line) {
            return substr($line, 0, 1) != '@';
        });
        return $this->parser->parse(trim(implode("\n", $lines)));
    }

    /**
     * @return Parameter[]
     */
    public function parameters() {
        $analyzer = new MethodAnalyzer($this->method);
        $parameters = [];
        foreach ($this->method->getParameters() as $parameter) {
            $type = $analyzer->getType($parameter, $this->types);
            $parameters[] = (new Parameter($parameter->name, $type, !$parameter->isDefaultValueAvailable()))
                ->setDescription($this->parser->parse($analyzer->getComment($parameter)));
        }
        return $parameters;
    }

    /**
     * Fills out partially available parameters
     *
     * @param array $parameters Available values indexed by name
     * @return array Filled values indexed by name
     */
    public function fill(array $parameters) {
        foreach ($this->method->getParameters() as $parameter) {
            if ($parameter->isDefaultValueAvailable() && !array_key_exists($parameter->name, $parameters)) {
                $parameters[$parameter->name] = $parameter->getDefaultValue();
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
        $injector = function () {
        };
        $filter = function () {
            return true;
        };

        $analyzer = new MethodAnalyzer($this->method);
        $arguments = $analyzer->fillParameters($parameters, $injector, $filter);

        return $this->method->invokeArgs($this->object, $arguments);
    }

    /**
     * @return boolean True if the action modifies the state of the application
     */
    public function isModifying() {
        return true;
    }
}