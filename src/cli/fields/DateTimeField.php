<?php
namespace rtens\domin\cli\fields;

use rtens\domin\cli\CliField;
use rtens\domin\Parameter;
use watoki\reflect\type\ClassType;

class DateTimeField implements CliField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        $type = $parameter->getType();
        return
            $type instanceof ClassType
            && (new \ReflectionClass($type->getClass()))->implementsInterface(\DateTimeInterface::class);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return \DateTime|\DateTimeImmutable
     */
    public function inflate(Parameter $parameter, $serialized) {
        $class = $this->getClass($parameter);
        return $serialized ? new $class($serialized) : null;
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
    }

    private function getClass(Parameter $parameter) {
        /** @var ClassType $type */
        $type = $parameter->getType();
        $class = $type->getClass();
        return $class;
    }
}