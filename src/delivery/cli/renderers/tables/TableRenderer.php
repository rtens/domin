<?php
namespace rtens\domin\delivery\cli\renderers\tables;

use League\CLImate\CLImate;
use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\renderers\tables\Table;

class TableRenderer implements Renderer {

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
        return $value instanceof Table && $this->canDrawTables();
    }

    /**
     * @param Table $value
     * @return mixed
     */
    public function render($value) {
        $climate = new CLImate();
        $climate->output->defaultTo('buffer');

        $data = $this->prepareData($value);
        if (!$data) {
            return '(empty)';
        }

        $climate->table($data);

        /** @var \League\CLImate\Util\Writer\Buffer $buffer */
        $buffer = $climate->output->get('buffer');

        return PHP_EOL . $buffer->get();
    }

    /**
     * @param Table $object
     * @return array
     */
    protected function prepareData($object) {
        $headers = $this->getHeaders($object);

        $data = [];
        foreach ($this->getRows($object) as $row) {
            $dataRow = [];
            foreach ($row as $col => $value) {
                $dataRow[$headers[$col]] = str_replace("\n", " ", $this->renderers->getRenderer($value)->render($value));
            }
            $data[] = $dataRow;
        }
        return $data;
    }

    protected function canDrawTables() {
        return (extension_loaded('mbstring') && class_exists(CLImate::class));
    }

    /**
     * @param Table $table
     * @return mixed
     */
    protected function getRows($table) {
        return $table->getItems();
    }

    /**
     * @param Table $table
     * @return mixed
     */
    protected function getHeaders($table) {
        return $table->getHeaders();
    }
}