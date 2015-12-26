<?php
namespace rtens\domin\reflection\types;

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

        } else if (strpos($hint, '::') && substr($hint, -1) == '*') {
            list($container, $constant) = explode('::', substr($hint, 0, -1));
            if ($container == 'self') {
                $reflection = $class;
            } else {
                $reflection = new \ReflectionClass($resolver->resolve($container));
            }

            $options = [];
            foreach ($reflection->getConstants() as $name => $value) {
                if (substr($name, 0, strlen($constant)) == $constant) {
                    $options[$value] = ucfirst($value);
                }
            }

            return new EnumerationType($options, new StringType());

        } else if (preg_match('#\[\d+;\d+(;\d+)?\]#', $hint)) {

            $parts = explode(';', substr($hint, 1, -1));
            $min = array_shift($parts);
            $max = array_shift($parts);
            $step = $parts ? array_shift($parts) : 1;

            return new RangeType($min, $max, $step);
        }

        return parent::fromTypeHint($hint, $class);
    }
}