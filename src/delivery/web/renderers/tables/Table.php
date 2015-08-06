<?php
namespace rtens\domin\delivery\web\renderers\tables;

interface Table {

    /**
     * @return string[] Header captions
     */
    public function getHeaders();

    /**
     * @return mixed[][] Rows containing the cells
     */
    public function getRows();
}