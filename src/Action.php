<?php
namespace rtens\domin;

interface Action {

    /**
     * @return string
     */
    public function caption();

    /**
     * @return string|null
     */
    public function description();

    /**
     * @return boolean True if the action modifies the state of the application
     */
    public function isModifying();

    /**
     * @return Parameter[]
     */
    public function parameters();

    /**
     * Fills out partially available parameters
     *
     * @param array $parameters Available values indexed by name
     * @return array Filled values indexed by name
     */
    public function fill(array $parameters);

    /**
     * @param mixed[] $parameters Values indexed by name
     * @return mixed the result of the execution
     * @throws \Exception if Action cannot be executed
     */
    public function execute(array $parameters);
}