<?php
namespace rtens\domin\delivery\web\renderers\charting;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebRenderer;

class ChartRenderer implements WebRenderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Chart;
    }

    /**
     * @param Chart $value
     * @return string
     */
    public function render($value) {
        $data = $value->data();

        if (!$data) {
            return '[no data]';
        }

        $data = json_encode($data);
        $options = json_encode($value->getOptions());

        $type = $value->chartType();
        $id = uniqid("$type-chart-");
        return (string)new Element('div', [], [
            new Element('canvas', ['id' => $id]),
            new Element('script', [], ["
                var ctx = document.getElementById('$id').getContext('2d');
                new Chart(ctx).$type($data, $options);
            "])
        ]);
    }

    /**
     * @param mixed $value
     * @return array|Element[]
     */
    public function headElements($value) {
        return [
            HeadElements::script('//cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js')
        ];
    }
}