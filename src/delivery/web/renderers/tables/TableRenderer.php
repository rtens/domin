<?php
namespace rtens\domin\delivery\web\renderers\tables;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebRenderer;
use watoki\reflect\Property;

class TableRenderer implements WebRenderer {

    /** @var RendererRegistry */
    private $renderers;

    public function __construct(RendererRegistry $renderers) {
        $this->renderers = $renderers;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Table;
    }

    /**
     * @param Table $value
     * @return mixed
     */
    public function render($value) {
        return (string)new Element('table', ['class' => 'table table-striped'], array_merge([
            new Element('thead', [], [new Element('tr', [], $this->renderHeaders($value))])
        ], $this->renderRows($value)));
    }

    private function renderHeaders($table) {
        $headers = [];
        foreach ($this->getHeaders($table) as $caption) {
            $headers[] = new Element('th', [], [$caption]);
        }
        return $headers;
    }

    private function renderRows($table) {
        $rows = [];
        foreach ($this->getRows($table) as $tableRow) {
            $row = [];
            foreach ($tableRow as $cell) {
                $row[] = new Element('td', [], [$this->renderers->getRenderer($cell)->render($cell)]);
            }

            $rows[] = new Element('tr', [], $row);
        }
        return $rows;
    }

    /**
     * @param Table $value
     * @return array|Element[]
     */
    public function headElements($value) {
        $elements = [];
        foreach ($this->getRows($value) as $row) {
            foreach ($row as $cell) {
                $renderer = $this->renderers->getRenderer($cell);
                if ($renderer instanceof WebRenderer) {
                    $elements = array_merge($elements, $renderer->headElements($value));
                }
            }
        }
        return $elements;
    }

    /**
     * @param Table $table
     * @return mixed
     */
    protected function getHeaders($table) {
        return $table->getHeaders();
    }

    /**
     * @param Table $table
     * @return mixed
     */
    protected function getRows($table) {
        return $table->getRows();
    }
}