<?php
namespace rtens\domin\execution;

class RedirectResult implements ExecutionResult {

    private $url;

    /**
     * @param string $url
     */
    public function __construct($url) {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }
}