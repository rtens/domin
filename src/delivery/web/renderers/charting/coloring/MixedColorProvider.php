<?php
namespace rtens\domin\delivery\web\renderers\charting\coloring;

use rtens\domin\parameters\Color;

class MixedColorProvider implements ColorProvider {

    private $colors;

    public function __construct() {
        $this->colors = [
            Color::BLUE(),
            Color::GREEN(),
            Color::RED(),
            Color::PURPLE(),
            Color::ORANGE(),
            Color::BROWN(),
            Color::PINK(),
            Color::YELLOW(),
            Color::GRAY()
        ];
    }

    /**
     * @return Color
     */
    public function next() {
        if (!$this->colors) {
            return Color::RANDOM();
        }
        return array_shift($this->colors);
    }
}