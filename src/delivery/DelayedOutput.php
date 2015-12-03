<?php namespace rtens\domin\delivery;

class DelayedOutput {

    /** @var callable */
    private $runner;

    /** @var callable */
    private $printer;

    /**
     * @param callable $runner Will be called with the object itself, returns void
     */
    public function __construct(callable $runner) {
        $this->runner = $runner;
        $this->printer = function ($string) {
            echo $string;
        };
    }

    public function write($message) {
        call_user_func($this->printer, $message);
    }

    public function writeLine($message) {
        $this->write($message . "\n");
    }

    function __toString() {
        call_user_func($this->runner, $this);
        return '';
    }

    public function surroundWith($before, $after) {
        $oldRunner = $this->runner;
        $this->runner = function (DelayedOutput $output) use ($oldRunner, $before, $after) {
            $this->write($before);
            call_user_func($oldRunner, $output);
            $this->write($after);
        };
    }

    /**
     * @param callable $printer
     */
    public function setPrinter(callable $printer) {
        $this->printer = $printer;
    }
}