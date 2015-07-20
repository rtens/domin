<?php
namespace rtens\domin\cli;

class Console {

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
}