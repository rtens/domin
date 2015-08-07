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

    public function writeLine($string = '') {
        $this->write($string . PHP_EOL);
    }

    public function getArguments() {
        return array_slice($this->argv, 1);
    }

    public function getOption($name) {
        foreach ($this->getArguments() as $i => $arg) {
            if (substr($arg, 0, 1) == '-' && ltrim($arg, '-') == $name && array_key_exists($i + 1, $this->argv)) {
                return $this->argv[$i + 1];
            }
        }
        throw new \InvalidArgumentException("No option [$name] given");
    }

    public function getScriptName() {
        return $this->argv[0];
    }
}