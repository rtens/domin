<?php
namespace rtens\domin\delivery\cli\renderers;

use rtens\domin\delivery\Renderer;

class DateTimeRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof \DateTimeInterface;
    }

    /**
     * @param \DateTime $value
     * @return string
     */
    public function render($value) {
        return $value->format('Y-m-d H:i:s P');
    }
}