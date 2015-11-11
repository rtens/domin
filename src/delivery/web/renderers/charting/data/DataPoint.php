<?php
namespace rtens\domin\delivery\web\renderers\charting\data;

use rtens\domin\parameters\Color;

class DataPoint {

    /** @var null|string */
    private $label;

    /** @var int */
    private $value;

    /** @var null|Color */
    private $color;

    /**
     * @param int $value
     * @param null|string $label
     * @param Color|null $color
     */
    public function __construct($value, $label = null, Color $color = null) {
        $this->label = $label;
        $this->value = $value;
        $this->color = $color;
    }

    /**
     * @return null|string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return int
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return null|Color
     */
    public function getColor() {
        return $this->color;
    }

}