<?php
namespace rtens\domin\reflection;

use watoki\reflect\ClassResolver;
use watoki\reflect\Type;
use watoki\reflect\type\StringType;

class TypeFactory extends \watoki\reflect\TypeFactory {

    /**
     * @param string $hint
     * @param \ReflectionClass $class
     * @return Type
     */
    public function fromTypeHint($hint, \ReflectionClass $class) {
        $resolver = new ClassResolver($class);

        if (strtolower(substr($hint, -3)) == '-id') {
            $target = $resolver->resolve(substr($hint, 0, -3));
            return new IdentifierType($target, new StringType());

        } else if (strpos($hint, '::') && substr($hint, -1) == '_') {
            list($container, $constant) = explode('::', $hint);
            if ($container == 'self') {
                $reflection = $class;
            } else {
                $reflection = new \ReflectionClass($resolver->resolve($container));
            }

            $options = [];
            foreach ($reflection->getConstants() as $name => $value) {
                if (substr($name, 0, strlen($constant)) == $constant) {
                    $options[] = $value;
                }
            }

            return new EnumerationType($options, new StringType());
        }

        return parent::fromTypeHint($hint, $class);
    }
}