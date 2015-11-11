<?php namespace rtens\domin\delivery\web\renderers\charting\charts;

use rtens\domin\delivery\web\renderers\charting\Chart;
use rtens\domin\parameters\Color;
use rtens\domin\delivery\web\renderers\charting\data\ScatterDataPoint;
use rtens\domin\delivery\web\renderers\charting\data\ScatterDataSet;

class ScatterChart extends Chart {

    /** @var array|ScatterDataSet[] */
    private $scatterData = [];

    /**
     * ScatterChart constructor.
     * @param array|ScatterDataSet[] $scatterData
     */
    public function __construct($scatterData) {
        parent::__construct();
        $this->scatterData = $scatterData;
    }

    protected function defaultOptions() {
        return array_merge(parent::defaultOptions(), [
            'showTooltips' => true,
            'scaleShowHorizontalLines' => true,
            'scaleShowLabels' => true,
            'scaleLabel' => "<%=value%>",
            'scaleArgLabel' => "<%=value%>",
            'multiTooltipTemplate' => '<%=datasetLabel%>: <%=arg%>; <%=value%>',
            'scaleBeginAtZero' => true,
            'datasetStroke' => false
        ]);
    }

    public function chartType() {
        return "Scatter";
    }

    public function makePalette(Color $color) {
        list($red, $green, $blue) = $color->asArray();
        return [
            'strokeColor' => "rgba($red,$green,$blue,1)",
            'pointColor' => "rgba($red,$green,$blue,1)",
            'pointStrokeColor' => "rgba($red,$green,$blue,0.8)",
        ];
    }

    public function data() {
        return array_map(function (ScatterDataSet $data) {
            return array_merge(
                [
                    'label' => $data->getLabel(),
                    'data' => array_map(function (ScatterDataPoint $point) {
                        return [
                            'x' => $point->getX(),
                            'y' => $point->getY(),
                            'r' => $point->getR(),
                        ];
                    }, $data->getDataPoints())
                ],
                $this->makePalette($data->getColor() ?: $this->provideColor()));
        }, $this->scatterData);
    }
}