<?php
namespace rtens\domin\web\renderers\link;

class ClassLink extends GenericLink {

    public function __construct($class, $actionId, callable $parameters = null) {
        parent::__construct($actionId, function ($object) use ($class) {
            return is_a($object, $class);
        }, $parameters);
    }
}