<?php
namespace rtens\domin\delivery\web\adapters\silex;

use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\web\BreadCrumb;
use rtens\domin\delivery\web\BreadCrumbsTrail;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class SilexBreadCrumbsTrail extends BreadCrumbsTrail {

    const COOKIE_NAME = 'domin_breadcrumbs';

    /**
     * @param ParameterReader $reader
     * @param Request $request
     */
    public function __construct($reader, $request) {
        parent::__construct($reader, $this->readCrumbs($request->cookies));
    }

    /**
     * @param ParameterBag $cookies
     * @return BreadCrumb[]
     */
    private function readCrumbs(ParameterBag $cookies) {
        if (!$cookies->has(self::COOKIE_NAME)) {
            return [];
        }

        $serialized = json_decode($cookies->get(self::COOKIE_NAME), true);
        return array_map(function ($item) {
            return new BreadCrumb($item['caption'], $item['target']);
        }, (array)$serialized);
    }

    public function getCookie() {
        return new Cookie(self::COOKIE_NAME, json_encode(array_map(function (BreadCrumb $crumb) {
            return [
                'target' => $crumb->getTarget(),
                'caption' => $crumb->getCaption()
            ];
        }, $this->getCrumbs())));
    }
}