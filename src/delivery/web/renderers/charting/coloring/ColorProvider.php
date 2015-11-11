<?php
namespace rtens\domin\delivery\web\renderers\charting\coloring;

use rtens\domin\parameters\Color;

interface ColorProvider {

    /**
     * @return Color
     */
    public function next();
}