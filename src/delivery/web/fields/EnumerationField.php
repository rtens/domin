<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\reflection\types\EnumerationType;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebField;

class EnumerationField implements WebField {

    /** @var FieldRegistry */
    private $fields;

    /**
     * EnumerationField constructor.
     * @param FieldRegistry $fields
     */
    public function __construct(FieldRegistry $fields) {
        $this->fields = $fields;
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() instanceof EnumerationType;
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        $param = new Parameter($parameter->getName(), $this->getOptionType($parameter));
        return $this->fields->getField($param)->inflate($param, $serialized);
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return (string)new Element('select', [
            'name' => $parameter->getName(),
            'class' => 'form-control'
        ], $this->renderOptions($parameter, $value));
    }

    private function renderOptions(Parameter $parameter, $value) {
        $options = [];
        foreach ($this->getOptions($parameter) as $key => $caption) {
            $options[] = new Element('option', array_merge([
                'value' => $key
            ], $key == $value ? [
                'selected' => 'selected'
            ] : []), [
                ucfirst((string)$caption)
            ]);
        }
        return $options;
    }

    /**
     * @param Parameter $parameter
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }

    /**
     * @param Parameter $parameter
     * @return array With captions indexed by values
     */
    protected function getOptions(Parameter $parameter) {
        $options = [];
        foreach ($this->getType($parameter)->getOptions() as $option) {
            $options[$option] = $option;
        }
        return $options;
    }

    /**
     * @param Parameter $parameter
     * @return EnumerationType
     */
    private function getType(Parameter $parameter) {
        return $parameter->getType();
    }

    /**
     * @param Parameter $parameter
     * @return \watoki\reflect\Type
     */
    protected function getOptionType(Parameter $parameter) {
        return $this->getType($parameter)->getOptionType();
    }
}