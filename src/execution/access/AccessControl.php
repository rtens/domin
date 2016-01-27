<?php
namespace rtens\domin\execution\access;

class AccessControl {

    /** @var AccessPolicy[] */
    private $policies = [];

    public function add(AccessPolicy $policy) {
        $this->policies[] = $policy;
    }

    public function isPermitted($actionId) {
        foreach ($this->policies as $policy) {
            if (!$policy->isPermitted($actionId)) {
                return false;
            }
        }
        return true;
    }

    public function isExecutionPermitted($actionId, array $parameters) {
        foreach ($this->policies as $policy) {
            if (!$policy->isExecutionPemitted($actionId, $parameters)) {
                return false;
            }
        }
        return true;
    }
}