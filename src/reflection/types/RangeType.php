<?php
namespace rtens\domin\reflection\types;

use watoki\reflect\Type;
use watoki\reflect\type\IntegerType;

class RangeType extends IntegerType {

    /** @var int */
    private $min;

    /** @var int */
    private $max;

    /** @var int */
    private $step;

    /**
     * @param int $min
     * @param int $max
     * @param int $step
     */
    public function __construct($min, $max, $step = 1) {
        $this->min = $min;
        $this->max = $max;
        $this->step = $step;
    }

    /**
     * @return int
     */
    public function getMin() {
        return $this->min;
    }

    /**
     * @return int
     */
    public function getMax() {
        return $this->max;
    }

    /**
     * @return int
     */
    public function getStep() {
        return $this->step;
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    public function is($value) {
        return parent::is($value) && $value >= $this->min && $value <= $this->max;
    }

    /**
     * @return string
     */
    public function __toString() {
        return "[{$this->min};{$this->max};{$this->step}]";
    }
}