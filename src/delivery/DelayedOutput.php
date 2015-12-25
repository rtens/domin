<?php namespace rtens\domin\delivery;

class DelayedOutput {

    /** @var callable */
    private $runner;

    /** @var callable */
    private $printer;

    /** @var callable */
    private $exceptionHandler;

    /**
     * @param callable $runner Will be called with the object itself, returns void
     */
    public function __construct(callable $runner) {
        $this->runner = $runner;
        $this->printer = function ($string) {
            echo $string;
        };
        $this->exceptionHandler = function (\Exception $exception) {
            $message = get_class($exception) . ': ' . $exception->getMessage() . ' ' .
                '[' . $exception->getFile() . ':' . $exception->getLine() . ']' . "\n" .
                $exception->getTraceAsString();

            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, $message);
            fclose($stderr);
            exit(1);
        };
    }

    function __toString() {
        try {
            call_user_func($this->runner, $this);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        return '';
    }

    public function write($message) {
        call_user_func($this->printer, $message);
    }

    public function writeLine($message) {
        $this->write($message . "\n");
    }

    /**
     * @param callable $printer
     */
    public function setPrinter(callable $printer) {
        $this->printer = $printer;
    }

    /**
     * @param callable $exceptionHandler
     */
    public function setExceptionHandler($exceptionHandler) {
        $this->exceptionHandler = $exceptionHandler;
    }

    public function handleException($message) {
        call_user_func($this->exceptionHandler, $message);
    }
}