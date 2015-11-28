<?php
namespace rtens\domin\parameters;

class Html {

    /** @var string */
    private $content;

    /**
     * @param string $content
     */
    public function __construct($content = '') {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content) {
        $this->content = $content;
    }

} 