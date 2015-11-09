<?php namespace rtens\domin\delivery\web\renderers\charting\data;

class ScatterDataPoint {

    /** @var double */
    private $x;

    /** @var double */
    private $y;

    /** @var double */
    private $r;

    /**
     * @param float $x
     * @param float $y
     * @param float $r
     */
    public function __construct($x, $y, $r = 1.0) {
        $this->x = $x;
        $this->y = $y;
        $this->r = $r;
    }

    /**
     * @return float
     */
    public function getX() {
        return $this->x;
    }

    /**
     * @return float
     */
    public function getY() {
        return $this->y;
    }

    /**
     * @return float
     */
    public function getR() {
        return $this->r;
    }

}