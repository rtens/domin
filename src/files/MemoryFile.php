<?php
namespace rtens\domin\files;

class MemoryFile implements File {

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var string */
    private $content;

    /**
     * @param string $name
     * @param string $type
     * @param string $content
     */
    function __construct($name, $type, $content = '') {
        $this->name = $name;
        $this->content = $content;
        $this->type = $type;
    }

    /**
     * @param string $content
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
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
        return strlen($this->content);
    }

    /**
     * @param string $path
     */
    public function save($path) {
        file_put_contents($path, $this->content);
    }
}