<?php
namespace rtens\domin\delivery\web\renderers\tables;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;

class ObjectTableRenderer extends TableRenderer {

    /** @var LinkPrinter */
    private $printer;

    public function __construct(RendererRegistry $renderers, LinkPrinter $printer) {
        parent::__construct($renderers);
        $this->printer = $printer;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof ObjectTable;
    }

    /**
     * @param ObjectTable|Table $value
     * @return mixed
     */
    public function render($value) {
        return parent::render($value);
    }

    /**
     * @param ObjectTable $table
     * @return mixed
     */
    protected function getHeaders($table) {
        return array_merge([''], $table->getHeaders());
    }

    /**
     * @param ObjectTable $table
     * @return mixed
     */
    protected function getRows($table) {
        $rows = [];
        foreach ($table->getObjects() as $object) {
            $rows[] = array_merge(
                [new Element('div', [], $this->printer->createDropDown($object))],
                $table->getCells($object));
        }
        return $rows;
    }

}