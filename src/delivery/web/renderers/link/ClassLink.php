<?php
namespace rtens\domin\delivery\web\renderers\link;

class ClassLink extends GenericLink {

    /**
     * @param string $class
     * @param string $actionId
     * @param callable|null $parameters
     */
    public function __construct($class, $actionId, callable $parameters = null) {
        parent::__construct($actionId, function ($object) use ($class) {
            return is_a($object, $class);
        }, $parameters);
    }
}