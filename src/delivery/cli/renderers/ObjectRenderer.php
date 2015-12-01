<?php
namespace rtens\domin\delivery\cli\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use watoki\reflect\PropertyReader;
use watoki\reflect\TypeFactory;

class ObjectRenderer implements Renderer {

    /** @var RendererRegistry */
    private $renderers;

    /** @var TypeFactory */
    private $types;

    public function __construct(RendererRegistry $renderers, TypeFactory $types) {
        $this->renderers = $renderers;
        $this->types = $types;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return is_object($value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function render($value) {
        if (method_exists($value, '__toString')) {
            return (string)$value;
        }

        $output = [
            '### ' . (new \ReflectionClass($value))->getShortName() . ' ###',
        ];

        $reader = new PropertyReader($this->types, $value);
        foreach ($reader->readInterface($value) as $property) {
            if (!$property->canGet()) {
                continue;
            }
            $propertyValue = $property->get($value);
            $rendered = $this->renderers->getRenderer($propertyValue)->render($propertyValue);

            $output[] = ucfirst($property->name()) . ': ' . rtrim(str_replace(PHP_EOL, PHP_EOL . '    ', $rendered));
        }

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}