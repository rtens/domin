<?php
namespace rtens\domin\web\renderers;

use rtens\domin\parameters\Image;

class ImageRenderer extends FileRenderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Image;
    }

    /**
     * @param Image $value
     * @return string
     */
    public function render($value) {
        return parent::render($value->getFile());
    }
}