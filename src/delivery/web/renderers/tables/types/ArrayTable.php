<?php
namespace rtens\domin\delivery\web\renderers\tables\types;

use rtens\domin\delivery\web\renderers\tables\Table;

class ArrayTable implements Table {

    /** @var array */
    private $array;

    /** @var string[] */
    private $columns = [];

    private $headers = [];

    private $filters = [];

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
            if (isset($this->headers[$column])) {
                $headers[] = $this->headers[$column];
            } else {
                $headers[] = ucfirst($column);
            }
        }
        return $headers;
    }

    /**
     * @return mixed[][] Rows containing the cells
     */
    public function getItems() {
        return $this->array;
    }

    /**
     * @param $item
     * @return mixed[] The cells of an item
     */
    public function getCells($item) {
        $row = [];
        foreach ($this->columns as $column) {
            if (array_key_exists($column, $item)) {
                $cell = $item[$column];

                if (isset($this->filters[$column])) {
                    $cell = call_user_func($this->filters[$column], $cell);
                }

                $row[] = $cell;
            } else {
                $row[] = '';
            }
        }
        return $row;
    }

    /**
     * @param $columns
     * @return static
     */
    public function selectColumns($columns) {
        foreach ($this->columns as $column) {
            if (!in_array($column, $columns)) {
                unset($this->columns[$column]);
            }
        }
        return $this;
    }

    /**
     * @param string $column
     * @param string $header
     * @return static
     */
    public function setHeader($column, $header) {
        $this->headers[$column] = $header;
        return $this;
    }

    /**
     * @param string $column
     * @param callable $filter
     * @return static
     */
    public function setFilter($column, callable $filter) {
        $this->filters[$column] = $filter;
        return $this;
    }
}