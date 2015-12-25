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

        $value->setPrinter(function ($string) {
            echo $string;

            $length = strlen($string);
            if (self::$bufferSize > $length) {
                echo str_repeat("\x00", self::$bufferSize - $length + 1);
            }

            flush();
            ob_flush();
        });
        $value->setExceptionHandler(function (\Exception $exception) {
            echo '</pre>';
            echo new Element('div', ['class' => 'alert alert-danger'], [
                htmlentities($exception->getMessage())
            ]);
        });

        return new DelayedOutput(function () use ($value) {
            $value->write('<pre>');
            $value->__toString();
            $value->write('</pre>');
        });
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