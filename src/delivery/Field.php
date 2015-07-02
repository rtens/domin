<?php
namespace rtens\domin\delivery;

interface Field {

    /**
     * @param string $type
     * @return bool
     */
    public function handles($type);

    /**
     * @param string $serialized
     * @return mixed
     */
    public function inflate($serialized);
}