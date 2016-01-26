<?php
namespace rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\delivery\ParameterReader;

class BreadCrumbsTrail {

    /** @var BreadCrumb[] */
    private $crumbs;

    /** @var ParameterReader */
    private $reader;

    /**
     * @param ParameterReader $reader
     * @param BreadCrumb[] $crumbs
     */
    public function __construct(ParameterReader $reader, array $crumbs) {
        $this->crumbs = $crumbs;
        $this->reader = $reader;
    }

    /**
     * @return bool
     */
    public function hasCrumbs() {
        return !empty($this->crumbs);
    }

    /**
     * @return array|BreadCrumb[]
     */
    public function getCrumbs() {
        return $this->crumbs;
    }

    /**
     * @return BreadCrumb
     * @throws \Exception If there are no crumbs
     */
    public function getLastCrumb() {
        if (!$this->hasCrumbs()) {
            throw new \Exception("There are no crumbs");
        }
        return $this->crumbs[count($this->crumbs) - 1];
    }

    /**
     * @param Action $action
     * @param string $actionId
     * @return array|BreadCrumb[]
     */
    public function updateCrumbs(Action $action, $actionId) {
        $current = new BreadCrumb($action->caption(), $this->makeTarget($actionId, $action));

        $newCrumbs = [];
        foreach ($this->crumbs as $crumb) {
            if ($crumb == $current) {
                break;
            }
            $newCrumbs[] = $crumb;
        }
        $newCrumbs[] = $current;

        $this->crumbs = $newCrumbs;
        return $newCrumbs;
    }

    public function reset() {
        $this->crumbs = [];
    }

    private function makeTarget($actionId, Action $action) {
        $target = $actionId;

        $parameters = $this->readRawParameters($action);
        if ($parameters) {
            $keyValues = [];
            foreach ($parameters as $key => $value) {
                $keyValues[] = urlencode($key) . '=' . urlencode($value);
            }
            $target .= '?' . implode('&', $keyValues);
        }

        return $target;
    }

    private function readRawParameters(Action $action) {
        $values = [];

        foreach ($action->parameters() as $parameter) {
            if ($this->reader->has($parameter)) {
                $values[$parameter->getName()] = $this->reader->read($parameter);
            }
        }
        return $values;
    }
}