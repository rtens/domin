<?php
namespace rtens\domin\reflection;

use watoki\reflect\ClassResolver;
use watoki\reflect\Type;

class TypeFactory extends \watoki\reflect\TypeFactory {

    /**
     * @param array|string[] $hints
     * @param \ReflectionClass $class
     * @return Type
     */
    public function fromTypeHints(array $hints, \ReflectionClass $class) {
        $resolver = new ClassResolver($class);

        foreach ($hints as $hint) {

            if (strtolower(substr($hint, -3)) == '-id') {
                $otherType = parent::fromTypeHints(array_diff($hints, [$hint]), $class);
                $target = $resolver->resolve(substr($hint, 0, -3));
                return new IdentifierType($target, $otherType);

            } else if (strpos($hint, '::') && substr($hint, -1) == '_') {
                list($container, $constant) = explode('::', $hint);
                $reflection = new \ReflectionClass($resolver->resolve($container));

                $options = [];
                foreach ($reflection->getConstants() as $name => $value) {
                    if (substr($name, 0, strlen($constant)) == $constant) {
                        $options[] = $value;
                    }
                }

                $otherType = parent::fromTypeHints(array_diff($hints, [$hint]), $class);
                return new EnumerationType($options, $otherType);
            }
        }
        return parent::fromTypeHints($hints, $class);
    }
}