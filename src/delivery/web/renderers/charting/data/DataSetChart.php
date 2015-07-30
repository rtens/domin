<?php
namespace rtens\domin\delivery\web\renderers\charting\data;

use rtens\domin\delivery\web\renderers\charting\Chart;

abstract class DataSetChart extends Chart {

    /** @var null|string[] */
    private $labels;

    /** @var DataSet[] */
    private $dataSets = [];

    /**
     * LineChart constructor.
     * @param null|string[] $labels
     * @param DataSet[] $dataSets
     */
    public function __construct(array $labels = null, array $dataSets = []) {
        parent::__construct();
        $this->labels = $labels;
        $this->dataSets = $dataSets;
    }

    public function data() {
        $dataSets = $this->getDataSets();
        if (!$dataSets) {
            return [];
        }
        return [
            "labels" => $this->makeLabels(),
            "datasets" => array_map(function (DataSet $set) {
                return array_merge(array(
                    "label" => $set->getLabel(),
                    "data" => $set->getValues()
                ), $this->makePalette($this->colors->next()));
            }, $dataSets)
        ];
    }

    /**
     * @return null|string[]
     */
    public function getLabels() {
        return $this->labels;
    }

    /**
     * @return DataSet[]
     */
    public function getDataSets() {
        return $this->dataSets;
    }

    /**
     * @param DataSet $set
     * @return $this
     */
    public function addDataSet(DataSet $set) {
        $this->dataSets[] = $set;
        return $this;
    }

    private function makeLabels() {
        if ($this->labels) {
            return $this->labels;
        }
        $count = 0;
        foreach ($this->dataSets as $set) {
            $count = max($count, count($set->getValues()));
        }

        return range(1, $count);
    }

}