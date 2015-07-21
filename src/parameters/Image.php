<?php
namespace rtens\domin\parameters;

class Image {

    private $file;

    function __construct(File $file) {
        $this->file = $file;
    }

    /**
     * @return File
     */
    public function getFile() {
        return $this->file;
    }
}