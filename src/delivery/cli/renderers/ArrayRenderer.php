<?php
namespace rtens\domin\delivery\cli\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;

class ArrayRenderer implements Renderer {

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
     * @param mixed $value
     * @return mixed
     */
    public function render($value) {
        $delimiter = PHP_EOL . PHP_EOL;

        $isList = array_keys($value) == range(0, count($value) - 1);

        $lines = [];
        foreach ($value as $key => $item) {
            $lines[] = ($isList ? '' : ($key . ': ')) . $this->renderers->getRenderer($item)->render($item);
        }

        return PHP_EOL . implode($delimiter, $lines) . PHP_EOL;
    }
}