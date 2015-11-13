<?php
namespace rtens\domin\delivery\cli\renderers\tables;

use rtens\domin\delivery\web\renderers\tables\types\ObjectTable;

class ObjectTableRenderer extends TableRenderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof ObjectTable && $this->canDrawTables();
    }

    /**
     * @param ObjectTable $table
     * @return mixed
     */
    protected function getRows($table) {
        $rows = [];
        foreach ($table->getItems() as $object) {
            $rows[] = $table->getCells($object);
        }
        return $rows;
    }

    /**
     * @param ObjectTable $table
     * @return mixed
     */
    protected function getHeaders($table) {
        return $table->getHeaders();
    }
}