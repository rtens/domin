<?php
namespace rtens\domin\delivery\web\renderers\charting\charts;

use rtens\domin\parameters\Color;

class BarChart extends DataSetChart {

    public function chartType() {
        return "Bar";
    }

    public function makePalette(Color $color) {
        list($red, $green, $blue) = $color->asArray();

        return [
            'fillColor' => "rgba($red,$green,$blue,1)",
            'strokeColor' => "rgba($red,$green,$blue,0)",
            'highlightFill' => "rgba($red,$green,$blue,0.8)",
            'highlightStroke' => "rgba($red,$green,$blue,0)",
        ];
    }
}