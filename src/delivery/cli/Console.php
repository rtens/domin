<?php
namespace rtens\domin\delivery\cli;

class Console {

    private $argv;

    public function __construct(array $argv) {
        $this->argv = $argv;
    }

    public function read($prompt = '') {
        $this->write($prompt);
        return trim(fgets(STDIN));
    }

    public function write($string) {
        echo $string;
    }

    public function error($message) {
        $stderr = fopen('php://stderr', 'w');
        fwrite($stderr, $message);
        fclose($stderr);
    }

    public function writeLine($string = '') {
        $this->write($string . PHP_EOL);
    }

    public function getArguments() {
        return array_values(array_slice($this->argv, 1));
    }

    public function getOption($name, $default = null) {
        if (!$this->hasOption($name)) {
            return $default;
        }

        return $this->getArguments()[$this->optionPosition($name)];
    }

    private function optionPosition($name) {
        $arguments = $this->getArguments();

        foreach ($arguments as $i => $arg) {
            if (substr($arg, 0, 1) == '-' && ltrim($arg, '-') == $name && array_key_exists($i + 1, $arguments)) {
                return $i + 1;
            }
        }

        return -1;
    }

    public function hasOption($name) {
        return $this->optionPosition($name) > -1;
    }

    public function getScriptName() {
        return $this->argv[0];
    }
}