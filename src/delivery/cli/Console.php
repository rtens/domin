<?php
namespace rtens\domin\delivery\cli;

class Console {

    private $argv;

    public function __construct(array $argv) {
        $this->argv = array_slice($argv, 1);
    }

    public function read($prompt = '') {
        $this->write($prompt);
        return trim(fgets(STDIN));
    }

    public function write($string) {
        echo $string;
    }

    public function writeLine($string = '') {
        $this->write($string . PHP_EOL);
    }

    public function getArguments() {
        return $this->argv;
    }

    public function getOption($name) {
        foreach ($this->argv as $i => $arg) {
            if (substr($arg, 0, 1) == '-' && ltrim($arg, '-') == $name && array_key_exists($i + 1, $this->argv)) {
                return $this->argv[$i + 1];
            }
        }
        throw new \InvalidArgumentException("No option [$name] given");
    }
}