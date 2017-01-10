<?php
namespace rtens\domin\reflection;

use rtens\domin\Action;
use rtens\domin\Parameter;
use rtens\domin\reflection\types\TypeFactory;
use watoki\reflect\MethodAnalyzer;

abstract class StaticMethodAction implements Action {

    /** @var \ReflectionMethod */
    protected $method;

    /** @var TypeFactory */
    protected $types;

    /** @var CommentParser */
    protected $parser;

    /**
     * @param \ReflectionMethod $method
     * @param TypeFactory $types
     * @param CommentParser $parser
     */
    public function __construct(\ReflectionMethod $method, TypeFactory $types, CommentParser $parser) {
        $this->method = $method;
        $this->types = $types;
        $this->parser = $parser;
    }

    /**
     * @return string
     */
    public function caption() {
        return
            $this->unCamelize($this->method->getDeclaringClass()->getShortName()) .
            ': ' . $this->unCamelize($this->method->name);
    }

    protected function unCamelize($camel) {
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

        $parameters = $this->initParameters();
        foreach ($this->method->getParameters() as $parameter) {
            $type = $analyzer->getType($parameter, $this->types);
            $parameters[] = (new Parameter($parameter->name, $type, !$parameter->isDefaultValueAvailable()))
                ->setDescription($this->parser->parse($analyzer->getComment($parameter)));
        }
        return $parameters;
    }

    /**
     * @return Parameter[]
     */
    protected function initParameters() {
        return [];
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
     * @return boolean True if the action modifies the state of the application
     */
    public function isModifying() {
        return true;
    }
}