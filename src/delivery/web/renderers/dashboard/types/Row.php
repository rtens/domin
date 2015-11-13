<?php
namespace rtens\domin\delivery\web\renderers\dashboard\types;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\dashboard\DashboardItem;

class Row implements DashboardItem {

    /** @var array|Column[] */
    private $columns;

    /**
     * @param array|Column[] $columns
     */
    public function __construct(array $columns) {
        $this->columns = $columns;
    }

    /**
     * @param RendererRegistry $renderers
     * @return Element
     */
    public function render(RendererRegistry $renderers) {
        return new Element('div', ['class' => 'row'], array_map(function (Column $column) use ($renderers) {
            return $column->render($renderers);
        }, $this->columns));
    }

    /**
     * @param RendererRegistry $renderers
     * @return \rtens\domin\delivery\web\Element[]
     */
    public function headElements(RendererRegistry $renderers) {
        $elements = [];
        foreach ($this->columns as $column) {
            $elements = array_merge($elements, $column->headElements($renderers));
        }
        return $elements;
    }
}