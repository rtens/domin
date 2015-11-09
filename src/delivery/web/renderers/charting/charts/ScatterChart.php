<?php namespace rtens\domin\delivery\web\renderers\charting\charts;

use rtens\domin\delivery\web\renderers\charting\Chart;
use rtens\domin\delivery\web\renderers\charting\data\ScatterData;
use rtens\domin\delivery\web\renderers\charting\data\ScatterDataPoint;

class ScatterChart extends Chart {

    /** @var array|ScatterData[] */
    private $scatterData = [];

    /**
     * ScatterChart constructor.
     * @param array|ScatterData[] $scatterData
     */
    public function __construct($scatterData) {
        parent::__construct();
        $this->scatterData = $scatterData;
    }

    public function chartType() {
        return "Scatter";
    }

    public function makePalette(array $rgb) {
        list($red, $green, $blue) = $rgb;
        return [
            'strokeColor' => "rgba($red,$green,$blue,1)",
            'pointColor' => "rgba($red,$green,$blue,1)",
            'pointStrokeColor' => "rgba($red,$green,$blue,0.8)",
        ];
    }

    public function data() {
        return array_map(function (ScatterData $data) {
            return array_merge([
                'label' => $data->getLabel(),
                'data' => array_map(function (ScatterDataPoint $point) {
                    return [
                        'x' => $point->getX(),
                        'y' => $point->getY(),
                        'r' => $point->getR(),
                    ];
                }, $data->getDataPoints())
            ], $this->makePalette($this->colors->next()));
        }, $this->scatterData);
    }
}