<?php
namespace rtens\domin\delivery\web\renderers\tables;

use rtens\domin\delivery\web\renderers\link\LinkPrinter;

class DataTable implements Table {

    /** @return array */
    private static $defaultOptions = [
        'order' => [],
        'stateSave' => true
    ];

    /** @var Table */
    private $table;

    /** @var array */
    private $options;

    public function __construct(Table $table, $options = []) {
        $this->table = $table;
        $this->options = array_merge(self::$defaultOptions, $options);
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @return string[] Header captions
     */
    public function getHeaders() {
        return $this->table->getHeaders();
    }

    /**
     * @param null|LinkPrinter $linkPrinter
     * @return \rtens\domin\delivery\web\Element[][] Rows containing the Element of each cell
     */
    public function getRows(LinkPrinter $linkPrinter = null) {
        return $this->table->getRows($linkPrinter);
    }
}