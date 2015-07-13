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
        $descriptions = [];

        $reader = new PropertyReader($value);
        foreach ($reader->readInterface($value) as $property) {
            if (!$property->canGet()) {
                continue;
            }

            $propertyValue = $property->get($value);

            $descriptions[] = new Element('dt', [], [
                htmlentities(ucfirst($property->name()))
            ]);
            $descriptions[] = new Element('dd', [], [
                $this->renderers->getRenderer($propertyValue)->render($propertyValue)
            ]);
        }

        return (string)new Element('div', ['class' => 'panel panel-info'], [
            new Element('div', ['class' => 'panel-heading'], [
                new Element('h3', ['class' => 'panel-title'], [
                    htmlentities((new \ReflectionClass($value))->getShortName())
                ])
            ]),
            new Element('div', ['class' => 'panel-body'], [
                new Element('dl', ['class' => 'dl-horizontal'], $descriptions)
            ])
        ]);
    }
}