<?php
namespace rtens\domin\delivery\web\menu;

use watoki\curir\delivery\WebRequest;

interface MenuItem {

    /**
     * @param WebRequest $request
     * @return \rtens\domin\delivery\web\Element
     */
    public function render(WebRequest $request);
}