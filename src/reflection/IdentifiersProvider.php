<?php
namespace rtens\domin\reflection;

class IdentifiersProvider {

    /** @var array|callable[] */
    private $providers = [];

    public function getIdentifiers($class) {
        if (!array_key_exists($class, $this->providers)) {
            throw new \Exception("No identifier provider registered for [$class]");
        }

        return call_user_func($this->providers[$class]);
    }

    public function setProvider($class, callable $provider) {
        $this->providers[$class] = $provider;
    }
}