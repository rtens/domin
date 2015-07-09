<?php
namespace rtens\domin;

interface Action {

    /**
     * @return string
     */
    public function caption();

    /**
     * @return mixed[]
     */
    public function parameters();

    /**
     * @param string $parameter Name of parameter
     * @return boolean
     */
    public function isRequired($parameter);

    /**
     * @param mixed[] $parameters Values indexed by name
     * @return mixed the result of the execution
     * @throws \Exception if Action cannot be executed
     */
    public function execute(array $parameters);
}