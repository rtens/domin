<?php
namespace rtens\domin\delivery\cli\renderers;

use rtens\domin\delivery\cli\renderers\tables\TableRenderer;
use rtens\domin\delivery\web\renderers\charting\Chart;
use rtens\domin\delivery\web\renderers\charting\data\DataPointChart;
use rtens\domin\delivery\web\renderers\charting\data\DataSetChart;

class ChartRenderer extends TableRenderer {
    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Chart && $this->canDrawTables();
    }

    /**
     * @param Chart $object
     * @return array
     */
    protected function prepareData($object) {
        if ($object instanceof DataPointChart) {
            $data = [];
            foreach ($object->getDataPoints() as $point) {
                $data[$point->getLabel()] = $point->getValue();
            }
            return [$data];
        } else if ($object instanceof DataSetChart) {
            $headers = $object->getLabels();

            $data = [];
            foreach ($object->getDataSets() as $set) {
                $dataSet = [
                    '' => $set->getLabel()
                ];
                foreach ($set->getValues() as $i => $value) {
                    $dataSet[$headers[$i]] = $value;
                }
                $data[] = $dataSet;
            }
            return $data;
        }
        throw new \InvalidArgumentException("Cannot render chart");
    }
}