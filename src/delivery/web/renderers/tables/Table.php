<?php
namespace rtens\domin\delivery\web\renderers\tables;

use rtens\domin\delivery\web\renderers\link\LinkPrinter;

interface Table {

    /**
     * @return string[] Header captions
     */
    public function getHeaders();

    /**
     * @param null|LinkPrinter $linkPrinter
     * @return \mixed[][] Rows containing the cells
     */
    public function getRows(LinkPrinter $linkPrinter = null);
}