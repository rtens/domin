<?php namespace rtens\domin\delivery\web\renderers\charting\data;

use rtens\domin\parameters\Color;

class ScatterDataSet {

    /** @var string */
    private $label;

    /** @var ScatterDataPoint[] */
    private $dataPoints;

    /** @var Color|null */
    private $color;

    /**
     * @param ScatterDataPoint[] $dataPoints
     * @param string $label
     * @param Color $color
     */
    public function __construct(array $dataPoints, $label = '', Color $color = null) {
        $this->label = $label;
        $this->dataPoints = $dataPoints;
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return ScatterDataPoint[]
     */
    public function getDataPoints() {
        return $this->dataPoints;
    }

    /**
     * @return null|Color
     */
    public function getColor() {
        return $this->color;
    }
}