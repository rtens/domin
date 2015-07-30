<?php
namespace rtens\domin\delivery\web\renderers\charting;

class ColorProvider {

    private $colors = [
        [93, 165, 218], // blue
        [96, 189, 104], // green
        [241, 88, 84], // red
        [178, 118, 178], // purple
        [250, 164, 58], // orange
        [178, 145, 47], // brown
        [241, 124, 176], // pink
        [222, 207, 63], // yellow
        [77, 77, 77], // gray
    ];

    public function next() {
        if (!$this->colors) {
            return [mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)];
        }
        return array_shift($this->colors);
    }
}