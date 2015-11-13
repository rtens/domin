<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\WebRenderer;

class MapRenderer implements WebRenderer {

    /** @var RendererRegistry */
    protected $renderers;

    /** @var LinkPrinter */
    private $links;

    public function __construct(RendererRegistry $renderers, LinkPrinter $links) {
        $this->renderers = $renderers;
        $this->links = $links;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return is_array($value);
    }

    /**
     * @param array $array
     * @return mixed
     */
    public function render($array) {
        $content = [
            new Element('div', ['class' => 'panel-body'], [
                $this->renderArray($array)
            ])
        ];

        $links = $this->links->createLinkElements($array);
        if ($links) {
            array_unshift($content, new Element('div', ['class' => 'panel-heading clearfix'], [
                new Element('small', ['class' => 'pull-right'], $links)
            ]));
        }
        return new Element('div', ['class' => 'panel panel-default'], $content);
    }

    public function renderArray(array $array) {
        $descriptions = [];

        foreach ($array as $key => $value) {
            $caption = htmlentities(ucfirst($key));
            $descriptions[] = new Element('dt', [], [
                is_null($value)
                    ? new Element('s', [], [$caption])
                    : $caption
            ]);
            $descriptions[] = new Element('dd', [], [
                $this->renderers->getRenderer($value)->render($value)
            ]);
        }

        return new Element('dl', ['class' => 'dl-horizontal', 'style' => 'margin-bottom: 0;'], $descriptions);
    }

    /**
     * @param array $array
     * @return array|Element[]
     */
    public function headElements($array) {
        $elements = [];
        foreach ($array as $item) {
            $renderer = $this->renderers->getRenderer($item);
            if ($renderer instanceof WebRenderer) {
                $elements = array_merge($elements, $renderer->headElements($item));
            }
        }
        return $elements;
    }
}