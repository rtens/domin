<?php
namespace rtens\domin\parameters;

class Color {

    /** @var int */
    private $red;

    /** @var int */
    private $green;

    /** @var int */
    private $blue;

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     */
    public function __construct($red, $green, $blue) {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    public static function fromHex($hexString) {
        list($r, $g, $b) = str_split(trim($hexString, '#'), 2);
        return new Color(hexdec($r), hexdec($g), hexdec($b));
    }

    public function asHex() {
        return '#'
        . str_pad(dechex($this->red), 2, '0', STR_PAD_LEFT)
        . str_pad(dechex($this->green), 2, '0', STR_PAD_LEFT)
        . str_pad(dechex($this->blue), 2, '0', STR_PAD_LEFT);
    }

    /**
     * @return int
     */
    public function getRed() {
        return $this->red;
    }

    /**
     * @return int
     */
    public function getGreen() {
        return $this->green;
    }

    /**
     * @return int
     */
    public function getBlue() {
        return $this->blue;
    }

    /**
     * @return array
     */
    public function asArray() {
        return [$this->red, $this->green, $this->blue];
    }


    public static function RANDOM() {
        return new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
    }

    public static function BLUE() {
        return new Color(93, 165, 218);
    }

    public static function GREEN() {
        return new Color(96, 189, 104);
    }

    public static function RED() {
        return new Color(241, 88, 84);
    }

    public static function PURPLE() {
        return new Color(178, 118, 178);
    }

    public static function ORANGE() {
        return new Color(250, 164, 58);
    }

    public static function BROWN() {
        return new Color(178, 145, 47);
    }

    public static function PINK() {
        return new Color(241, 124, 176);
    }

    public static function YELLOW() {
        return new Color(222, 207, 63);
    }

    public static function GRAY() {
        return new Color(77, 77, 77);
    }
}