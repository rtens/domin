<?php
namespace rtens\domin\delivery\web\renderers\tables;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebRenderer;

class DataTableRenderer implements WebRenderer {

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

        return new Element('div', ['class' => 'data-table'], [
            $this->renderers->getRenderer($table)->render($table),
        ]);
    }

    /**
     * @param DataTable $value
     * @return array|Element[]
     */
    public function headElements($value) {
        $elements = [];
        $table = $value->getTable();

        $renderer = $this->renderers->getRenderer($table);
        if ($renderer instanceof WebRenderer) {
            $elements = $renderer->headElements($table);
        }

        $options = json_encode($value->getOptions());

        $elements[] = HeadElements::style('//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css');
        $elements[] = HeadElements::script('//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js');
        $elements[] = new Element('script', [], ["
            $(function () {
                $('.data-table table').dataTable($options);
            });
        "]);

        return $elements;
    }


}