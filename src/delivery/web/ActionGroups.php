<?php
namespace rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;

class ActionGroups {

    /** @var ActionRegistry */
    private $actions;

    /** @var array[] */
    private $groups = [];

    /**
     * @param ActionRegistry $actions
     */
    public function __construct(ActionRegistry $actions) {
        $this->actions = $actions;
    }

    /**
     * @param string $actionId
     * @param string $groupName
     */
    public function put($actionId, $groupName) {
        $this->groups[$groupName][] = $actionId;
    }

    /**
     * @return string[]
     */
    public function getGroups() {
        return array_keys($this->groups);
    }

    /**
     * @param string $group
     * @return Action[] indexed by ID
     * @throws \Exception
     */
    public function getActionsOf($group) {
        $actions = [];
        foreach ($this->groups[$group] as $id) {
            $actions[$id] = $this->actions->getAction($id);
        }
        return $actions;
    }
}