<?php
namespace rtens\domin\reflection;

class Identifier {

    private $target;

    private $id;

    function __construct($target, $id) {
        $this->target = $target;
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getTarget() {
        return $this->target;
    }
}