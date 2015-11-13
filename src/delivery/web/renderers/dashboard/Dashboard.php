<?php
namespace rtens\domin\delivery\web\renderers\dashboard;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;

class Dashboard implements DashboardItem {

    /** @var array|Row[] */
    private $rows;

    /**
     * @param array|Row[] $rows
     */
    public function __construct(array $rows) {
        $this->rows = $rows;
    }

    /**
     * @param RendererRegistry $renderers
     * @return Element
     */
    public function render(RendererRegistry $renderers) {
        return new Element('div', [], array_map(function (Row $row) use ($renderers) {
            return $row->render($renderers);
        }, $this->rows));
    }

    /**
     * @param RendererRegistry $renderers
     * @return \rtens\domin\delivery\web\Element[]
     */
    public function headElements(RendererRegistry $renderers) {
        $elements = [];
        foreach ($this->rows as $row) {
            $elements = array_merge($elements, $row->headElements($renderers));
        }
        return $elements;
    }
}