<?php
namespace rtens\domin\cli\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\reflection\Identifier;

class IdentifierRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Identifier;
    }

    /**
     * @param Identifier $value
     * @return mixed
     */
    public function render($value) {
        return $value->getId();
    }
}