<?php
namespace rtens\domin\delivery\web\renderers\charting\data;

class DataPoint {

    /** @var null|string */
    private $label;

    /** @var int */
    private $value;

    /**
     * @param int $value
     * @param null|string $label
     */
    public function __construct($value, $label = null) {
        $this->label = $label;
        $this->value = $value;
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
}