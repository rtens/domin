<?php
namespace rtens\domin\delivery\web\renderers\charting;

abstract class Chart {

    /** @var ColorProvider */
    protected $colors;

    public function __construct() {
        $this->colors = new ColorProvider();
    }

    abstract public function chartType();

    abstract public function makePalette(array $rgb);

    abstract public function data();

}