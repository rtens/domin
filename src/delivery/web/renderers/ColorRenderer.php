<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebRenderer;
use rtens\domin\parameters\Color;

class ColorRenderer implements WebRenderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Color;
    }

    /**
     * @param Color $value
     * @return mixed
     */
    public function render($value) {
        if (!$value) {
            return null;
        }

        return (string)new Element('span', [
            'style' => 'padding: 2pt 6pt; background-color: ' . $value->asHex()
        ], [$value->asHex()]);
    }

    /**
     * @param mixed $value
     * @return array|Element[]
     */
    public function headElements($value) {
        return [];
    }
}