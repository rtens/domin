<?php
namespace rtens\domin\delivery;

use watoki\reflect\ValuePrinter;

class RendererRegistry {

    /** @var array|Renderer[] */
    private $renderers = [];

    /**
     * @param mixed $value
     * @return Renderer
     * @throws \Exception
     */
    public function getRenderer($value) {
        foreach ($this->renderers as $renderer) {
            if ($renderer->handles($value)) {
                return $renderer;
            }
        }

        throw new \Exception("No Renderer found to handle " . ValuePrinter::serialize($value));
    }

    public function add(Renderer $renderer) {
        $this->renderers[] = $renderer;
    }
} 