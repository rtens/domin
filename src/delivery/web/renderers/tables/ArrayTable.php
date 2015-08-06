<?php
namespace rtens\domin\delivery\web\renderers\tables;

use rtens\domin\delivery\web\renderers\link\LinkPrinter;

class ArrayTable implements Table {

    /** @var array */
    private $array;

    /** @var string[] */
    private $columns = [];

    public function __construct(array $array) {
        $this->array = $array;

        foreach ($this->array as $item) {
            foreach ($item as $key => $value) {
                $this->columns[$key] = $key;
            }
        }
    }

    /**
     * @return string[] Header captions
     */
    public function getHeaders() {
        $headers = [];
        foreach ($this->columns as $column) {
            $headers[] = ucfirst($column);
        }
        return $headers;
    }

    /**
     * @param null|LinkPrinter $linkPrinter
     * @return \string[][] Rows containing the cells
     */
    public function getRows(LinkPrinter $linkPrinter = null) {
        $rows = [];
        foreach ($this->array as $item) {
            $row = [];
            foreach ($this->columns as $column) {
                if (array_key_exists($column, $item)) {
                    $row[] = $item[$column];
                } else {
                    $row[] = '';
                }
            }
            $rows[] = $row;
        }
        return $rows;
    }
}