<?php
namespace rtens\domin\delivery\web\renderers\charting\charts;

use rtens\domin\delivery\web\renderers\charting\data\DataSetChart;

class BarChart extends DataSetChart {

    public function chartType() {
        return "Bar";
    }

    public function makePalette(array $rgb) {
        list($red, $green, $blue) = $rgb;

        return [
            'fillColor' => "rgba($red,$green,$blue,0.5)",
            'strokeColor' => "rgba($red,$green,$blue,0.8)",
            'highlightFill' => "rgba($red,$green,$blue,0.75)",
            'highlightStroke' => "rgba($red,$green,$blue,1)",
        ];
    }
}