<?php
namespace rtens\domin\delivery\web\renderers\charting\charts;

use rtens\domin\parameters\Color;

class PieChart extends DataPointChart {

    public function chartType() {
        return 'Pie';
    }

    public function makePalette(Color $color) {
        list($red, $green, $blue) = $color->asArray();
        return [
            'color' => "rgba($red,$green,$blue,1)",
            'highlight' => "rgba($red,$green,$blue,0.8)",
        ];
    }
}