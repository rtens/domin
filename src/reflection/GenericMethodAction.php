<?php
namespace rtens\domin\reflection;

class GenericMethodAction extends MethodAction {

    private $afterExecute;
    private $caption;
    private $fill;

    /**
     * @param callable $callback Filter for return value of execute()
     * @return static
     */
    public function setAfterExecute(callable $callback) {
        $this->afterExecute = $callback;
        return $this;
    }

    public function execute(array $parameters) {
        $return = parent::execute($parameters);
        if ($this->afterExecute) {
            $return = call_user_func($this->afterExecute, $return);
        }
        return $return;
    }

    public function setCaption($caption) {
        $this->caption = $caption;
    }

    public function caption() {
        if ($this->caption) {
            return $this->caption;
        }
        return parent::caption();
    }

    public function setFill(callable $callback) {
        $this->fill = $callback;
    }

    public function fill(array $parameters) {
        $parameters = parent::fill($parameters);
        if ($this->fill) {
            $parameters = call_user_func($this->fill, $parameters);
        }
        return $parameters;
    }
}