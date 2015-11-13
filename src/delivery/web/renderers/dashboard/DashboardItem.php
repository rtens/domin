<?php
namespace rtens\domin\delivery\web\renderers\dashboard;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;

interface DashboardItem {

    /**
     * @param RendererRegistry $renderers
     * @return Element
     */
    public function render(RendererRegistry $renderers);

    /**
     * @param RendererRegistry $renderers
     * @return \rtens\domin\delivery\web\Element[]
     */
    public function headElements(RendererRegistry $renderers);
}