<?php
namespace rtens\domin\execution\access;

class AccessControl {

    /** @var AccessRestriction[] */
    private $restrictions = [];

    public function add(AccessRestriction $restriction) {
        $this->restrictions[] = $restriction;
    }

    public function isPermitted($actionId) {
        foreach ($this->restrictions as $policy) {
            if ($policy->isRestricted($actionId)) {
                return false;
            }
        }
        return true;
    }

    public function isExecutionPermitted($actionId, array $parameters) {
        foreach ($this->restrictions as $policy) {
            if ($policy->isExecutionRestricted($actionId, $parameters)) {
                return false;
            }
        }
        return true;
    }
}