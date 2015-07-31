<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\table\TableConfiguration;
use rtens\domin\delivery\web\renderers\table\TableConfigurationRegistry;
use rtens\domin\delivery\web\WebRenderer;
use rtens\domin\reflection\types\TypeFactory;
use watoki\reflect\Property;

class ObjectListRenderer implements WebRenderer {

    /** @var RendererRegistry */
    private $renderers;

    /** @var TypeFactory */
    private $types;

    /** @var LinkPrinter */
    private $links;

    /** @var TableConfigurationRegistry */
    private $config;

    public function __construct(RendererRegistry $renderers, TypeFactory $types,
                                LinkPrinter $links, TableConfigurationRegistry $config) {
        $this->renderers = $renderers;
        $this->types = $types;
        $this->links = $links;
        $this->config = $config;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return is_array($value) && count($value) > 1 && !empty($value[0]) && is_object($value[0])
        && $this->usesObjectRenderer($value[0]) && $this->isHomogeneous($value);
    }

    private function usesObjectRenderer($object) {
        return $this->renderers->getRenderer($object) instanceof ObjectRenderer;
    }

    private function isHomogeneous(array $value) {
        $class = get_class($value[0]);
        foreach ($value as $object) {
            if (!is_object($object) || get_class($object) != $class) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function render($value) {
        $config = $this->config->getConfiguration($value[0]);

        $properties = $config->getProperties($value[0]);

        return (string)new Element('table', ['class' => 'table table-striped data-table'], array_merge([
                new Element('thead', [], [new Element('tr', [], $this->renderHeaders($properties, $config))])
            ], $this->renderRows($value, $properties, $config)));
    }

    /**
     * @param Property[] $properties
     * @param TableConfiguration $config
     * @return array
     */
    private function renderHeaders($properties, TableConfiguration $config) {
        $headers = [new Element('th')];
        foreach ($properties as $property) {
            $headers[] = new Element('th', [], [$config->getHeaderCaption($property)]);
        }
        return $headers;
    }

    /**
     * @param array|object[] $list
     * @param Property[] $properties
     * @param TableConfiguration $config
     * @return array
     * @throws \Exception
     */
    private function renderRows($list, $properties, TableConfiguration $config) {
        $rows = [];
        foreach ($list as $item) {
            $row = [new Element('td', [], $this->links->createDropDown($item))];

            foreach ($properties as $property) {
                $propertyValue = $config->getValue($property, $item);
                $renderer = $this->renderers->getRenderer($propertyValue);

                $row[] = new Element('td', [], [$renderer->render($propertyValue)]);
            }

            $rows[] = new Element('tr', [], $row);
        }
        return $rows;
    }

    /**
     * @param mixed $value
     * @return array|Element[]
     */
    public function headElements($value) {
        $object = $value[0];

        $config = $this->config->getConfiguration($object);
        $properties = $config->getProperties($object);

        $elements = [
            HeadElements::style('//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css'),
            HeadElements::script('//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js'),
            new Element('script', [], ["
                $(function () {
                    $('.data-table').dataTable({
                        columnDefs: [ { targets: 0, orderable: false } ],
                        order: [],
                        stateSave: true
                    });
                });
            "])
        ];
        foreach ($properties as $property) {
            $propertyValue = $config->getValue($property, $object);
            $renderer = $this->renderers->getRenderer($propertyValue);

            if ($renderer instanceof WebRenderer) {
                $elements = array_merge($elements, $renderer->headElements($propertyValue));
            }
        }
        return $elements;
    }
}