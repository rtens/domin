<?php
namespace rtens\domin\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\web\Element;
use watoki\reflect\PropertyReader;

class ObjectRenderer implements Renderer {

    /** @var RendererRegistry */
    private $renderers;

    function __construct(RendererRegistry $renderers) {
        $this->renderers = $renderers;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return is_object($value);
    }

    /**
     * @param object $value
     * @return mixed
     */
    public function render($value) {
        $output = [
            (string)new Element('h3', [], [
                get_class($value)
            ])
        ];

        $reader = new PropertyReader($value);
        foreach ($reader->readInterface($value) as $property) {
            $propertyValue = $property->get($value);

            $output[] = (string)new Element('div', [], [
                new Element('h4', [], [
                    $property->name()
                ]),
                new Element('p', [], [
                    $this->renderers->getRenderer($propertyValue)->render($propertyValue)
                ])
            ]);
        }

        return implode("\n", $output);
    }
}