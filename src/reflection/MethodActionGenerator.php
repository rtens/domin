<?php
namespace rtens\domin\reflection;

use rtens\domin\ActionRegistry;

class MethodActionGenerator {

    /** @var ActionRegistry */
    private $actions;

    /** @var TypeFactory */
    private $types;

    public function __construct(ActionRegistry $actions, TypeFactory $types) {
        $this->actions = $actions;
        $this->types = $types;
    }

    public function fromObject($object) {
        $class = new \ReflectionClass($object);

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic() || substr($method->getName(), 0, 1) == '_') {
                continue;
            }
            $this->actions->add(self::getId($method),
                new GenericMethodAction($object, $method->getName(), $this->types));
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
        return $method->getDeclaringClass()->getShortName() . ':' . $method->getName();
    }

    public static function actionId($class, $method) {
        return self::getId(new \ReflectionMethod($class, $method));
    }
}