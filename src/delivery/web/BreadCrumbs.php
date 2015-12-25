<?php
namespace rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\delivery\ParameterReader;
use watoki\collections\Map;
use watoki\curir\cookie\Cookie;
use watoki\curir\cookie\CookieStore;
use watoki\curir\delivery\WebRequest;

class BreadCrumbs {

    const COOKIE_KEY = 'domin_trail';

    /** @var WebRequest */
    private $request;

    /** @var CookieStore */
    private $cookies;

    /**
     * @param CookieStore $cookies
     * @param WebRequest $request
     */
    public function __construct(CookieStore $cookies, WebRequest $request) {
        $this->request = $request;
        $this->cookies = $cookies;
    }

    public function readCrumbs() {
        if ($this->cookies->hasKey(self::COOKIE_KEY)) {
            return $this->cookies->read(self::COOKIE_KEY)->payload;
        }
        return [];
    }

    public function getLastCrumb() {
        $crumbs = $this->readCrumbs();
        if (!$crumbs) {
            return null;
        }
        return end($crumbs)['target'];
    }

    public function updateCrumbs(Action $action, $actionId) {
        $reader = new RequestParameterReader($this->request);

        $crumbs = $this->readCrumbs();

        $current = [
            'target' => (string)$this->request->getContext()
                ->appended($actionId)
                ->withParameters(new Map($this->readRawParameters($action, $reader))),
            'caption' => $action->caption()
        ];
        $newCrumbs = [];
        foreach ($crumbs as $crumb) {
            if ($crumb == $current) {
                break;
            }
            $newCrumbs[] = $crumb;
        }
        $newCrumbs[] = $current;

        $this->saveCrumbs($newCrumbs);
        return $newCrumbs;
    }

    public function reset() {
        $this->cookies->create(new Cookie([]), self::COOKIE_KEY);
    }

    private function readRawParameters(Action $action, ParameterReader $reader) {
        $values = [];

        foreach ($action->parameters() as $parameter) {
            if ($reader->has($parameter)) {
                $values[$parameter->getName()] = $reader->read($parameter);
            }
        }
        return $values;
    }

    private function saveCrumbs($crumbs) {
        $this->cookies->create(new Cookie($crumbs), self::COOKIE_KEY);
    }
}