<?php
namespace rtens\domin\delivery\web\adapters\curir;

use rtens\domin\Action;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\web\BreadCrumb;
use rtens\domin\delivery\web\BreadCrumbsTrail;
use watoki\curir\cookie\Cookie;
use watoki\curir\cookie\CookieStore;

class CurirBreadCrumbsTrail extends BreadCrumbsTrail {

    const COOKIE_KEY = 'domin_trail';

    /** @var CookieStore */
    private $cookies;

    public function __construct(ParameterReader $reader, CookieStore $cookies) {
        parent::__construct($reader, $this->readCrumbs($cookies));
        $this->cookies = $cookies;
    }

    public function updateCrumbs(Action $action, $actionId) {
        return $this->saveCrumbs(parent::updateCrumbs($action, $actionId));
    }

    /**
     * @param CookieStore $cookies
     * @return BreadCrumb[]
     */
    private function readCrumbs(CookieStore $cookies) {
        if ($cookies->hasKey(self::COOKIE_KEY)) {
            return array_map(function ($array) {
                return new BreadCrumb($array['caption'], $array['target']);
            }, $cookies->read(self::COOKIE_KEY)->payload);
        }
        return [];
    }

    /**
     * @param BreadCrumb[] $crumbs
     * @return BreadCrumb[]
     */
    private function saveCrumbs($crumbs) {
        $this->cookies->create(new Cookie(array_map(function (BreadCrumb $crumb) {
            return [
                'caption' => $crumb->getCaption(),
                'target' => $crumb->getTarget()
            ];
        }, $crumbs)), self::COOKIE_KEY);
        return $crumbs;
    }
}