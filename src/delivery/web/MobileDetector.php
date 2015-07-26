<?php
namespace rtens\domin\delivery\web;

use Detection\MobileDetect;
use watoki\factory\Factory;

class MobileDetector {

    /** @var MobileDetect */
    private $detect;

    /**
     * @param Factory $factory <-
     */
    function __construct(Factory $factory) {
        if (class_exists(MobileDetect::class)) {
            $this->detect = $factory->getInstance(MobileDetect::class);
        }
    }

    /**
     * @return bool
     */
    public function isMobile() {
        if (!$this->detect) {
            return true;
        }
        return $this->detect->isMobile() || $this->detect->isTablet();
    }

}