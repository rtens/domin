<?php
namespace rtens\domin\cli\renderers;

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
        $delimiter = PHP_EOL;

        return PHP_EOL . implode($delimiter, array_map(function ($item) {
            return $this->renderers->getRenderer($item)->render($item);
        }, $value));
    }
}