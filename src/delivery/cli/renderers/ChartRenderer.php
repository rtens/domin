<?php
namespace rtens\domin\delivery\cli\renderers;

use rtens\domin\delivery\cli\renderers\tables\TableRenderer;
use rtens\domin\delivery\web\renderers\charting\Chart;
use rtens\domin\delivery\web\renderers\charting\charts\DataPointChart;
use rtens\domin\delivery\web\renderers\charting\charts\DataSetChart;
use rtens\domin\delivery\web\renderers\charting\charts\ScatterChart;

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
            return $this->prepareDataPoints($object);
        } else if ($object instanceof DataSetChart) {
            return $this->prepareDataSets($object);
        } else if ($object instanceof ScatterChart) {
            return $this->prepareScatterDate($object);
        }
        throw new \InvalidArgumentException("Cannot render [" . get_class($object) . "]");
    }

    private function prepareDataPoints(DataPointChart $chart) {
        $data = [];
        foreach ($chart->getDataPoints() as $i => $point) {
            $data[$point->getLabel() ?: 'val' . $i] = $point->getValue();
        }
        return [$data];
    }

    protected function prepareDataSets(DataSetChart $object) {
        $headers = $object->getLabels();

        $data = [];
        foreach ($object->getDataSets() as $i => $set) {
            $dataSet = [
                '' => $set->getLabel() ?: 'set' . $i
            ];
            foreach ($set->getValues() as $j => $value) {
                $dataSet[$headers[$j] ?: 'val' . $j] = $value;
            }
            $data[] = $dataSet;
        }
        return $data;
    }

    private function prepareScatterDate(ScatterChart $chart) {
        $data = [];

        foreach ($chart->getScatterData() as $set) {
            foreach ($set->getDataPoints() as $point) {
                $data[] = [
                    '' => $set->getLabel(),
                    'x' => $point->getX(),
                    'y' => $point->getY(),
                    'r' => $point->getR()
                ];
            }
        }

        return $data;
    }
}