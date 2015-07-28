<?php
namespace rtens\domin\delivery\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\table\TableConfiguration;
use rtens\domin\delivery\web\renderers\table\TableConfigurationRegistry;
use rtens\domin\reflection\types\TypeFactory;
use watoki\reflect\Property;

class ObjectArrayRenderer implements Renderer {

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
        return is_array($value) && !empty($value[0]) && is_object($value[0])
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

        return (string)new Element('table', ['class' => 'table table-striped'], array_merge([
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
     * @param array|object[] $value
     * @param Property[] $properties
     * @param TableConfiguration $config
     * @return array
     * @throws \Exception
     */
    private function renderRows($value, $properties, TableConfiguration $config) {
        $rows = [];
        foreach ($value as $object) {
            $row = [new Element('td', [], $this->links->createDropDown($object))];

            foreach ($properties as $property) {
                $propertyValue = $config->getValue($property, $object);
                $renderer = $this->renderers->getRenderer($propertyValue);

                $row[] = new Element('td', [], [$renderer->render($propertyValue)]);
            }

            $rows[] = new Element('tr', [], $row);
        }
        return $rows;
    }
}