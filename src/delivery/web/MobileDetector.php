<?php
namespace rtens\domin\delivery\web;

use Detection\MobileDetect;

class MobileDetector {

    /**
     * @return bool
     */
    public function isMobile() {
        if (class_exists(MobileDetect::class)) {
            $detect = new MobileDetect();
            return $detect->isMobile() || $detect->isTablet();
        }
        return true;
    }

}