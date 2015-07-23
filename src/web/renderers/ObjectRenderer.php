<?php
namespace rtens\domin\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\web\Element;
use rtens\domin\web\renderers\link\LinkPrinter;
use watoki\reflect\PropertyReader;
use watoki\reflect\TypeFactory;

class ObjectRenderer implements Renderer {

    /** @var RendererRegistry */
    private $renderers;

    /** @var LinkPrinter */
    private $links;

    /** @var TypeFactory */
    private $types;

    public function __construct(RendererRegistry $renderers, TypeFactory $types, LinkPrinter $links) {
        $this->renderers = $renderers;
        $this->types = $types;
        $this->links = $links;
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

        $reader = new PropertyReader($this->types, get_class($value));
        foreach ($reader->readInterface($value) as $property) {
            if (!$property->canGet()) {
                continue;
            }

            $propertyValue = $property->get($value);

            $caption = htmlentities(ucfirst($property->name()));
            $descriptions[] = new Element('dt', [], [
                is_null($propertyValue)
                    ? new Element('s', [], [$caption])
                    : $caption
            ]);
            $descriptions[] = new Element('dd', [], [
                $this->renderers->getRenderer($propertyValue)->render($propertyValue)
            ]);
        }

        return (string)new Element('div', ['class' => 'panel panel-info'], [
            new Element('div', ['class' => 'panel-heading clearfix'], [
                new Element('h3', ['class' => 'panel-title'], [
                    htmlentities((new \ReflectionClass($value))->getShortName()),
                    new Element('small', ['class' => 'pull-right'], $this->links->createLinkElements($value))
                ])
            ]),
            new Element('div', ['class' => 'panel-body'], [
                new Element('dl', ['class' => 'dl-horizontal'], $descriptions)
            ])
        ]);
    }
}