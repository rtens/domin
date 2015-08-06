<?php
namespace rtens\domin\delivery\cli\renderers\tables;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\renderers\tables\DataTable;

class DataTableRenderer implements Renderer {

    /** @var RendererRegistry */
    private $renderers;

    public function __construct(RendererRegistry $renderers) {
        $this->renderers = $renderers;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof DataTable;
    }


    /**
     * @param DataTable $value
     * @return mixed
     */
    public function render($value) {
        $table = $value->getTable();
        return $this->renderers->getRenderer($table)->render($table);
    }
}