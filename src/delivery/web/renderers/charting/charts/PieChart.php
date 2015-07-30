<?php
namespace rtens\domin\delivery\web\renderers\charting\charts;

use rtens\domin\delivery\web\renderers\charting\data\DataPointChart;

class PieChart extends DataPointChart {

    public function chartType() {
        return 'Pie';
    }

    public function makePalette(array $rgb) {
        list($red, $green, $blue) = $rgb;
        return [
            'color' => "rgba($red,$green,$blue,1)",
            'highlight' => "rgba($red,$green,$blue,0.8)",
        ];
    }
}