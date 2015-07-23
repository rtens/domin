<?php
namespace rtens\domin\delivery\cli;

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