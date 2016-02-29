<?php
namespace rtens\domin\delivery\web\home;


use rtens\domin\delivery\web\ActionGroups;

class ActionList {

    /** @var array|ActionListItem[] */
    private $actions;

    /** @var ActionGroups */
    private $groups;

    /**
     * @param ActionGroups $groups
     * @param ActionListItem[] $actions
     */
    public function __construct(ActionGroups $groups, array $actions) {
        $this->actions = $actions;
        $this->groups = $groups;
    }

    public function hasGroups() {
        return count($this->groups->getGroups()) != 0;
    }

    public function getAllActions() {
        return $this->actions;
    }

    public function getGroups() {
        return $this->groups->getGroups();
    }

    public function getActionsOf($group) {
        $actions = array_keys($this->groups->getActionsOf($group));
        return array_filter($this->actions, function (ActionListItem $action) use ($actions) {
            return in_array($action->getId(), $actions);
        });
    }
}