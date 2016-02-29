<?php
namespace rtens\domin\delivery\web\home;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\web\ActionGroups;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\execution\access\AccessControl;
use rtens\domin\Parameter;
use rtens\domin\reflection\CommentParser;

class ListActions implements Action {

    /** @var ActionRegistry */
    private $actions;
    
    /** @var ActionGroups */
    private $groups;

    /** @var AccessControl */
    private $access;

    /** @var CommentParser */
    private $parser;

    /**
     * @param ActionRegistry $actions
     * @param ActionGroups $groups
     * @param AccessControl $access
     * @param CommentParser $parser
     */
    public function __construct(ActionRegistry $actions, ActionGroups $groups, AccessControl $access, CommentParser $parser) {
        $this->actions = $actions;
        $this->groups = $groups;
        $this->access = $access;
        $this->parser = $parser;
    }

    /**
     * @return string
     */
    public function caption() {
        return 'Actions';
    }

    /**
     * @return string|null
     */
    public function description() {
        return null;
    }

    /**
     * @return boolean True if the action modifies the state of the application
     */
    public function isModifying() {
        return false;
    }

    /**
     * @return Parameter[]
     */
    public function parameters() {
        return [];
    }

    /**
     * Fills out partially available parameters
     *
     * @param array $parameters Available values indexed by name
     * @return array Filled values indexed by name
     */
    public function fill(array $parameters) {
        return [];
    }

    /**
     * @param mixed[] $parameters Values indexed by name
     * @return mixed the result of the execution
     * @throws \Exception if Action cannot be executed
     */
    public function execute(array $parameters) {
        return new ActionList($this->groups, $this->assembleActions());
    }

    private function assembleActions() {
        $actions = [];
        foreach ($this->actions->getAllActions() as $id => $action) {
            if ($id != WebApplication::INDEX_ACTION && $this->access->isPermitted($id)) {
                $actions[] = new ActionListItem(
                    $id,
                    $action->caption(),
                    $this->parser->shorten($action->description())
                );
            }
        }
        return $actions;
    }
}