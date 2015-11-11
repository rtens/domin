<?php
namespace rtens\domin\delivery\web\renderers\charting\charts;

use rtens\domin\parameters\Color;

class LineChart extends DataSetChart {

    public function chartType() {
        return "Line";
    }

    public function makePalette(Color $color) {
        list($red, $green, $blue) = $color->asArray();

        $light = "rgba($red,$green,$blue,0.1)";
        $full = "rgba($red,$green,$blue,1)";

        return [
            'fillColor' => $light,
            'strokeColor' => $full,
            'pointColor' => $full,
            'pointStrokeColor' => "#fff",
            'pointHighlightFill' => "#fff",
            'pointHighlightStroke' => $full,
        ];
    }
}