<?php
namespace rtens\domin;

class ActionRegistry {

    /** @var null|AccessControl */
    private $access;

    /** @var array|Action[] indexed by ID */
    private $actions = [];

    /**
     * @return Action[] indexed by ID
     */
    public function getAllActions() {
        $actions = $this->actions;

        if ($this->access) {
            foreach ($actions as $id => $action) {
                if (!$this->access->isVisible($id)) {
                    unset($actions[$id]);
                }
            }
        }

        return $actions;
    }

    /**
     * @param string $id
     * @return Action
     * @throws \Exception
     */
    public function getAction($id) {
        if (!array_key_exists($id, $this->actions) || $this->access && !$this->access->isVisible($id)) {
            throw new \Exception("Action [$id] is not registered.");
        }

        return $this->actions[$id];
    }

    /**
     * @param string $id
     * @param Action $action
     * @throws \Exception
     * @return Action
     */
    public function add($id, Action $action) {
        if (array_key_exists($id, $this->actions)) {
            throw new \Exception("Action [$id] is already registered.");
        }

        $this->actions[$id] = $action;

        return $action;
    }

    /**
     * @param AccessControl $access
     */
    public function restrictAccess(AccessControl $access) {
        $this->access = $access;
    }

} 