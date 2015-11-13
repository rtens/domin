<?php
namespace rtens\domin\delivery\web\renderers\dashboard;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebRenderer;

class DashboardItemRenderer implements WebRenderer {

    /** @var RendererRegistry */
    private $renderers;

    /**
     * @param RendererRegistry $renderers
     */
    public function __construct(RendererRegistry $renderers) {
        $this->renderers = $renderers;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof DashboardItem;
    }

    /**
     * @param DashboardItem $value
     * @return string
     */
    public function render($value) {
        return (string)$value->render($this->renderers);
    }

    /**
     * @param DashboardItem $value
     * @return array|Element[]
     */
    public function headElements($value) {
        return $value->headElements($this->renderers);
    }
}