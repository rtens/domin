<?php
namespace rtens\domin\web\menu;

class MenuGroup {

    private $caption;
    private $items = [];

    function __construct($caption) {
        $this->caption = $caption;
    }

    public function add(MenuItem $item) {
        $this->items[] = $item;
        return $this;
    }

    public function getCaption() {
        return $this->caption;
    }

    public function getItems() {
        return $this->items;
    }
}