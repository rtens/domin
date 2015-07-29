<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
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
        $map = [];

        $reader = new PropertyReader($this->types, get_class($value));
        foreach ($reader->readInterface($value) as $property) {
            if (!$property->canGet()) {
                continue;
            }

            $map[$property->name()] = $property->get($value);
        }

        return (string)new Element('div', ['class' => 'panel panel-info'], [
            new Element('div', ['class' => 'panel-heading clearfix'], [
                new Element('h3', ['class' => 'panel-title'], [
                    htmlentities((new \ReflectionClass($value))->getShortName()),
                    new Element('small', ['class' => 'pull-right'], $this->links->createLinkElements($value))
                ])
            ]),
            new Element('div', ['class' => 'panel-body'], [
                (new MapRenderer($this->renderers))->render($map)
            ])
        ]);
    }
}