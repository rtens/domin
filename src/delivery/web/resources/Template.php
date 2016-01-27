<?php
namespace rtens\domin\delivery\web\resources;

class Template {

    /** @var string */
    private $file;

    /**
     * @param string $file
     */
    public function __construct($file) {
        $this->file = $file;
    }

    public function render($viewModel) {
        global $model;
        $model = $viewModel;

        ob_start();
        include $this->file;
        return ob_get_clean();
    }
}