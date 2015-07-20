<?php
namespace rtens\domin\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\reflection\Identifier;
use rtens\domin\web\Element;

class IdentifierRenderer implements Renderer {

    /** @var \rtens\domin\web\renderers\link\LinkPrinter */
    private $links;

    public function __construct($links) {
        $this->links = $links;
    }

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
        return $value->getId() . new Element('span', ['class' => 'pull-right'], $this->links->createLinkElements($value));
    }
}