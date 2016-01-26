<?php
namespace rtens\domin\execution;

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
        $url = $this->target;
        if ($this->parameters) {
            $keyValues = [];
            foreach ($this->parameters as $key => $value) {
                $keyValues[] = urlencode($key) . '=' . urlencode($value);
            }
            $url .= '?' . implode('&', $keyValues);
        }
        return $url;
    }
}