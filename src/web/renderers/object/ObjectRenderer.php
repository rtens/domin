<?php
namespace rtens\domin\web\renderers\object;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\web\Element;
use watoki\collections\Map;
use watoki\curir\protocol\Url;
use watoki\reflect\PropertyReader;

class ObjectRenderer implements Renderer {

    /** @var RendererRegistry */
    private $renderers;

    /** @var LinkRegistry */
    private $links;

    /** @var \watoki\curir\protocol\Url */
    private $baseUrl;

    function __construct(RendererRegistry $renderers, LinkRegistry $links, Url $baseUrl) {
        $this->renderers = $renderers;
        $this->links = $links;
        $this->baseUrl = $baseUrl;
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
            new Element('div', ['class' => 'panel-heading clearfix'], [
                new Element('h3', ['class' => 'panel-title'], [
                    htmlentities((new \ReflectionClass($value))->getShortName()),
                    new Element('small', ['class' => 'pull-right'], $this->getLinkedActions($value))
                ])
            ]),
            new Element('div', ['class' => 'panel-body'], [
                new Element('dl', ['class' => 'dl-horizontal'], $descriptions)
            ])
        ]);
    }

    /**
     * @param object $object
     * @return \rtens\domin\web\Element[]
     */
    protected function getLinkedActions($object) {
        return array_map(function (Link $link) use ($object) {
            $url = $this->baseUrl->appended($link->actionId())->withParameters(new Map($link->parameters($object)));
            return new Element('a', ['class' => 'btn btn-xs btn-primary', 'href' => $url], [
                $link->caption($object)
            ]);
        }, $this->links->getLinks($object));
    }
}