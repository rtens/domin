<?php
namespace rtens\domin;

interface Action {

    /**
     * @return string
     */
    public function caption();

    /**
     * @return Parameter[]
     */
    public function parameters();

    /**
     * @param mixed[] $parameters Values indexed by name
     * @return mixed the result of the execution
     * @throws \Exception if Action cannot be executed
     */
    public function execute(array $parameters);
}