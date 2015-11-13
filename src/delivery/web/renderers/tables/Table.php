<?php
namespace rtens\domin\delivery\web\renderers\tables;

interface Table {

    /**
     * @return string[] Header captions
     */
    public function getHeaders();

    /**
     * @return mixed[] The table items
     */
    public function getItems();

    /**
     * @param $item
     * @return mixed[] The cells of an item
     */
    public function getCells($item);
}