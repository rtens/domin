<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;

class MapRenderer implements Renderer {

    /** @var RendererRegistry */
    private $renderers;

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
     * @param mixed $array
     * @return mixed
     */
    public function render($array) {
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

        return (string)new Element('dl', ['class' => 'dl-horizontal'], $descriptions);
    }
}