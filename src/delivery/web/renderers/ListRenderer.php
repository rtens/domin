<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebRenderer;

class ListRenderer implements WebRenderer {

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
        return is_array($value) && $this->areNumerical(array_keys($value));
    }

    private function areNumerical($keys) {
        foreach ($keys as $key) {
            if (!is_numeric($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $value
     * @return mixed
     */
    public function render($value) {
        $items = [];
        foreach ($value as $item) {
            $items[] = new Element('li', [], [
                $this->renderers->getRenderer($item)->render($item)
            ]);
        }
        return (string)new Element('ul', ['class' => 'list-unstyled'], $items);
    }

    /**
     * @param mixed $value
     * @return array|Element[]
     */
    public function headElements($value) {
        $elements = [];
        foreach ($value as $item) {
            $renderer = $this->renderers->getRenderer($item);
            if ($renderer instanceof WebRenderer) {
                $elements = array_merge($elements, $renderer->headElements($item));
            }
        }
        return $elements;
    }
}