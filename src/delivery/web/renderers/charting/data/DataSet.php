<?php
namespace rtens\domin\delivery\web\renderers\charting\data;

class DataSet {

    /** @var null|string */
    private $label;

    /** @var int[] */
    private $values = [];

    /**
     * DataSet constructor.
     * @param null|string $label
     * @param int[] $values
     */
    public function __construct($label = null, array $values = []) {
        $this->label = $label;
        $this->values = $values;
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
}