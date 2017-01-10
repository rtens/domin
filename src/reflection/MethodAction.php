<?php
namespace rtens\domin\reflection;

use rtens\domin\reflection\types\TypeFactory;
use watoki\reflect\MethodAnalyzer;

class MethodAction extends StaticMethodAction {

    /** @var object */
    private $object;

    /**
     * @param object $object
     * @param string $method
     * @param TypeFactory $types
     * @param CommentParser $parser
     */
    public function __construct($object, $method, TypeFactory $types, CommentParser $parser) {
        parent::__construct(new \ReflectionMethod(get_class($object), $method), $types, $parser);
        $this->object = $object;
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
}