<?php
namespace rtens\domin\delivery\web;

class BreadCrumb {

    /** @var string */
    private $target;

    /** @var string */
    private $caption;

    /**
     * @param string $caption
     * @param string $target
     */
    public function __construct($caption, $target) {
        $this->caption = $caption;
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getCaption() {
        return $this->caption;
    }

    /**
     * @return string
     */
    public function getTarget() {
        return $this->target;
    }
}