<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\parameters\Identifier;
use rtens\domin\delivery\web\Element;

class IdentifierRenderer implements Renderer {

    /** @var LinkPrinter */
    private $links;

    public function __construct(LinkPrinter $links) {
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
        $caption = (new \ReflectionClass($value->getTarget()))->getShortName();
        $out = $value->getId();

        $dropDown = $this->links->createDropDown($value, $caption);
        if ($dropDown) {
            $out .= new Element('span', ['class' => 'pull-right'], $dropDown);
        }

        return $out;
    }
}