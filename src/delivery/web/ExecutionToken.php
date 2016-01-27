<?php
namespace rtens\domin\delivery\web;

class ExecutionToken {

    const SEPARATOR = '_';
    const DEFAULT_TIMEOUT_SEC = 600;

    /** @var int */
    private $timeoutSec = self::DEFAULT_TIMEOUT_SEC;

    /** @var string */
    private $secret;

    /**
     * @param string $secret
     */
    public function __construct($secret) {
        $this->secret = $secret;
    }

    public function generate($actionId) {
        $time = time();
        return $this->hash($actionId, $time) . self::SEPARATOR . $time;
    }

    public function isValid($token, $actionId) {
        list($hash, $time) = explode(self::SEPARATOR, $token);
        return $this->hash($actionId, $time) == $hash && $time > time() - $this->timeoutSec;
    }

    private function hash($actionId, $time) {
        return md5($this->secret . $actionId . $time);
    }

    public function setTimeout($sec) {
        $this->timeoutSec = $sec;
        return $this;
    }
}