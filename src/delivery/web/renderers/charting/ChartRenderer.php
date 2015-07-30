<?php
namespace rtens\domin\delivery\web\renderers\charting;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;

class ChartRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Chart;
    }

    /**
     * @return array
     */
    protected function getOptions() {
        return [
            'animation' => false,
            'responsive' => true,
            "multiTooltipTemplate" => "<%= datasetLabel %>: <%= value %>",
        ];
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
        $options = json_encode($this->getOptions());

        $type = $value->chartType();
        $id = uniqid("$type-chart-");
        return (string)new Element('div', [], [
            new Element('canvas', ['id' => $id]),
            HeadElements::script('//cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js'),
            new Element('script', [], ["
                var ctx = document.getElementById('$id').getContext('2d');
                new Chart(ctx).$type($data, $options);
            "])
        ]);
    }

}