<?php
namespace rtens\domin\reflection;

use rtens\domin\ActionRegistry;
use rtens\domin\reflection\types\TypeFactory;

class MethodActionGenerator {

    private $actions;
    private $types;
    private $parser;

    public function __construct(ActionRegistry $actions, TypeFactory $types, CommentParser $parser) {
        $this->actions = $actions;
        $this->types = $types;
        $this->parser = $parser;
    }

    public function fromObject($object) {
        $class = new \ReflectionClass($object);

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic() || substr($method->name, 0, 1) == '_') {
                continue;
            }
            $this->actions->add(self::getId($method),
                new GenericMethodAction($object, $method->name, $this->types, $this->parser));
        }

        return $this;
    }

    public function configure($object, $method, callable $callback) {
        $callback($this->get($object, $method));
        return $this;
    }

    private function get($object, $method) {
        $method = new \ReflectionMethod(get_class($object), $method);
        return $this->actions->getAction(self::getId($method));
    }

    private static function getId(\ReflectionMethod $method) {
        return $method->getDeclaringClass()->getShortName() . '_' . $method->name;
    }

    public static function actionId($class, $method) {
        return self::getId(new \ReflectionMethod($class, $method));
    }
}