<?php
namespace rtens\domin\delivery\web\renderers\charting\data;

use rtens\domin\delivery\web\renderers\charting\Chart;

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
        return array_map(function (DataPoint $set) {
            return array_merge([
                "label" => $set->getLabel(),
                "value" => $set->getValue()
            ], $this->makePalette($this->colors->next()));
        }, $this->getDataPoints());
    }

    /**
     * @return DataPoint[]
     */
    public function getDataPoints() {
        return $this->dataPoints;
    }

}