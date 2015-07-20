<?php
namespace rtens\domin\parameters;

class SavedFile implements File {

    /** @var string */
    private $path;

    /** @var string */
    private $type;

    /** @var string */
    private $name;

    /**
     * @param string $path
     * @param string $name
     * @param string $type
     */
    public function __construct($path, $name, $type) {
        $this->path = $path;
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getContent() {
        return file_get_contents($this->path);
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getSize() {
        return filesize($this->path);
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $content
     */
    public function setContent($content) {
        file_put_contents($this->path, $content);
    }

    /**
     * @param string $path
     */
    public function save($path) {
        copy($this->path, $path);
    }
}