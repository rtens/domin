<?php
namespace rtens\domin\delivery\web\menu;

interface MenuItem {

    /**
     * @return \rtens\domin\delivery\web\Element
     */
    public function render();
}