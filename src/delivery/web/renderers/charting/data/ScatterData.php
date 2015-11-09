<?php namespace rtens\domin\delivery\web\renderers\charting\data;

class ScatterData {

    /** @var string */
    private $label;

    /** @var ScatterDataPoint[] */
    private $dataPoints;

    /**
     * @param string $label
     * @param ScatterDataPoint[] $dataPoints
     */
    public function __construct($label, $dataPoints) {
        $this->label = $label;
        $this->dataPoints = $dataPoints;
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
}