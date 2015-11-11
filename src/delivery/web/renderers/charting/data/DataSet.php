<?php
namespace rtens\domin\delivery\web\renderers\charting\data;

use rtens\domin\parameters\Color;

class DataSet {

    /** @var null|string */
    private $label;

    /** @var int[] */
    private $values = [];

    /** @var null|Color */
    private $color;

    /**
     * @param int[] $values
     * @param null|string $label
     * @param Color|null $color
     */
    public function __construct(array $values = [], $label = '', Color $color = null) {
        $this->label = $label;
        $this->values = $values;
        $this->color = $color;
    }

    /**
     * @return null|string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return int[]
     */
    public function getValues() {
        return $this->values;
    }

    /**
     * @return null|Color
     */
    public function getColor() {
        return $this->color;
    }
}