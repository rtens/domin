<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebRenderer;

class MapRenderer implements WebRenderer {

    /** @var RendererRegistry */
    protected $renderers;

    public function __construct(RendererRegistry $renderers) {
        $this->renderers = $renderers;
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
        return new Element('div', ['class' => 'panel panel-default'], [
            new Element('div', ['class' => 'panel-body'], [
                $this->renderArray($array)
            ])
        ]);
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