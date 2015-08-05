<?php
namespace rtens\domin\delivery\web\renderers\tables;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;

class DataTableRenderer extends TableRenderer {
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
        return new Element('div', ['class' => 'data-table'], [
            parent::render($value),
        ]);
    }

    /**
     * @param DataTable $value
     * @return array|Element[]
     */
    public function headElements($value) {
        $elements = parent::headElements($value);

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