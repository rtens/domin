<?php
namespace rtens\domin\delivery\cli\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\parameters\File;

class FileRenderer implements Renderer {

    private $fileDir;

    public function __construct($fileDir) {
        $this->fileDir = $fileDir;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof File;
    }

    /**
     * @param File $value
     * @return mixed
     */
    public function render($value) {
        return ($this->fileDir ? $this->fileDir . DIRECTORY_SEPARATOR : '') .$value->getName();
    }
}