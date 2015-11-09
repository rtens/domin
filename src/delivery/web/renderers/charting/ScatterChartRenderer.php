<?php namespace rtens\domin\delivery\web\renderers\charting;

use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\renderers\charting\charts\ScatterChart;

class ScatterChartRenderer extends ChartRenderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return parent::handles($value) && $value instanceof ScatterChart;
    }

    /**
     * @return array
     */
    protected function getOptions() {
        return array_merge(parent::getOptions(), [
            'showTooltips' => true,
            'scaleShowHorizontalLines' => true,
            'scaleShowLabels' => true,
            'scaleLabel' => "<%=value%>",
            'scaleArgLabel' => "<%=value%>",
            'scaleBeginAtZero' => true,
            'datasetStroke' => false
        ]);
    }

    /**
     * @param mixed $value
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function headElements($value) {
        $elements = parent::headElements($value);
        $elements[] = HeadElements::script('http://dima117.github.io/Chart.Scatter/Chart.Scatter.min.js');
        return $elements;
    }
}