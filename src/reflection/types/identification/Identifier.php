<?php
namespace rtens\domin\reflection\types\identification;

class Identifier {

    private $target;

    private $id;

    public function __construct($target, $id) {
        $this->target = $target;
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getTarget() {
        return $this->target;
    }

    public function __toString() {
        return $this->id . ':' . $this->target;
    }
}