<?php
namespace rtens\domin\delivery;

interface ParameterReader {

    /**
     * @param string $name
     * @return string|null The serialized paramater
     */
    public function read($name);
}