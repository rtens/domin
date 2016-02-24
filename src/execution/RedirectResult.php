<?php
namespace rtens\domin\execution;

use rtens\domin\delivery\web\Url;

class RedirectResult implements ExecutionResult {

    /** @var string */
    private $target;

    /** @var array|\string[] */
    private $parameters;

    /**
     * @param string $target
     * @param string[] $parameters
     */
    public function __construct($target, array $parameters = []) {
        $this->target = $target;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return (string)Url::relative($this->target, $this->parameters);
    }
}