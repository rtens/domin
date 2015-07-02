<?php
namespace rtens\domin;

use watoki\collections\Map;

interface Action {

    /**
     * @return string
     */
    public function caption();

    /**
     * @return Map|string[] Parameter types indexed by name
     */
    public function parameters();

    /**
     * @param Map|mixed[] $parameters Values indexed by name
     * @return mixed the result of the execution
     * @throws \Exception if Action cannot be executed
     */
    public function execute(Map $parameters);
}