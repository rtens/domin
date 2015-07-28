<?php
namespace rtens\domin\delivery\web\renderers\table;

use watoki\reflect\ValuePrinter;

class TableConfigurationRegistry {

    /** @var TableConfiguration[] */
    private $configs = [];

    /**
     * @param $object
     * @return TableConfiguration
     * @throws \Exception
     */
    public function getConfiguration($object) {
        foreach ($this->configs as $config) {
            if ($config->appliesTo($object)) {
                return $config;
            }
        }

        throw new \Exception("No table configuration applying to " . ValuePrinter::serialize($object) . " found");
    }

    public function add(TableConfiguration $config) {
        $this->configs[] = $config;
    }
}