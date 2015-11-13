<?php
namespace rtens\domin\delivery\web\renderers\tables\types;

use rtens\domin\delivery\web\renderers\tables\Table;

class DataTable {

    /** @return array */
    private static $defaultOptions = [
        'order' => [],
        'stateSave' => true
    ];

    /** @var Table|ObjectTable */
    private $table;

    /** @var array */
    private $options;

    public function __construct($table, $options = []) {
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
     * @return ObjectTable|Table
     */
    public function getTable() {
        return $this->table;
    }
}