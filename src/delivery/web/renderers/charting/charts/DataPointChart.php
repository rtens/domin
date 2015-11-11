<?php
namespace rtens\domin\delivery\web\renderers\charting\charts;

use rtens\domin\delivery\web\renderers\charting\Chart;
use rtens\domin\delivery\web\renderers\charting\data\DataPoint;

abstract class DataPointChart extends Chart {

    /** @var DataPoint[] */
    private $dataPoints = [];

    /**
     * @param DataPoint[] $dataPoints
     */
    public function __construct(array $dataPoints = []) {
        parent::__construct();
        $this->dataPoints = $dataPoints;
    }

    public function data() {
        return array_map(function (DataPoint $point) {
            return array_merge(
                [
                    "label" => $point->getLabel(),
                    "value" => $point->getValue()
                ],
                $this->makePalette($point->getColor() ?: $this->provideColor()));
        }, $this->getDataPoints());
    }

    /**
     * @return DataPoint[]
     */
    public function getDataPoints() {
        return $this->dataPoints;
    }
}