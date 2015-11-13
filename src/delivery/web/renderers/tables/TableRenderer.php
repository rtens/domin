<?php
namespace rtens\domin\delivery\web\renderers\tables;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\WebRenderer;
use watoki\reflect\Property;

class TableRenderer implements WebRenderer {

    /** @var RendererRegistry */
    private $renderers;

    /** @var LinkPrinter */
    private $printer;

    public function __construct(RendererRegistry $renderers, LinkPrinter $printer) {
        $this->renderers = $renderers;
        $this->printer = $printer;
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
        $rows = $this->renderRows($value);

        if (!$rows) {
            return null;
        };

        return (string)new Element('table', ['class' => 'table table-striped'], array_merge([
            new Element('thead', [], [new Element('tr', [], $this->renderHeaders($value))])
        ], $rows));
    }

    private function renderHeaders($table) {
        $headers = [new Element('th', ['width' => '1'])];
        foreach ($this->getHeaders($table) as $caption) {
            $headers[] = new Element('th', [], [$caption]);
        }
        return $headers;
    }

    /**
     * @param Table $table
     * @return array
     * @throws \Exception
     */
    private function renderRows($table) {
        $rows = [];
        foreach ($table->getItems() as $item) {
            $row = [new Element('td', [], $this->printer->createDropDown($item))];
            foreach ($table->getCells($item) as $cell) {
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
        foreach ($value->getItems() as $item) {
            foreach ($value->getCells($item) as $cell) {
                $renderer = $this->renderers->getRenderer($cell);
                if ($renderer instanceof WebRenderer) {
                    $elements = array_merge($elements, $renderer->headElements($cell));
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
}