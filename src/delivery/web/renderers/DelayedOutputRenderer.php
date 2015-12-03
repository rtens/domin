<?php namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\DelayedOutput;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebRenderer;

class DelayedOutputRenderer implements WebRenderer {

    /**
     * @var int If >0, the buffer is filled with null bytes to be forced to flush.
     */
    public static $bufferSize = 0;

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof DelayedOutput;
    }

    /**
     * @param DelayedOutput $value
     * @return DelayedOutput
     */
    public function render($value) {
        header('Content-Encoding: none;');
        header('X-Accel-Buffering: no');
        ob_end_flush();

        $value->surroundWith("<pre>", "</pre>");
        $value->setPrinter(function ($string) {
            echo $string;

            $length = strlen($string);
            if (self::$bufferSize > $length) {
                echo str_repeat("\x00", self::$bufferSize - $length);
            }

            flush();
            ob_flush();
        });
        return $value;
    }

    /**
     * @param mixed $value
     * @return array|Element[]
     */
    public function headElements($value) {
        return [
            new Element('script', [], ['
                var scrolled = true;

                var keepScrolling = setInterval(function () {
                    scrolled = true;
                    window.scrollTo(0,document.body.scrollHeight);
                }, 100);

                window.addEventListener("scroll", function () {
                    if (!scrolled) {
                        clearInterval(keepScrolling);
                    }
                    scrolled = false;
                });
            '])
        ];
    }
}