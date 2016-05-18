<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebField;
use rtens\domin\Parameter;

abstract class AutoCompleteField implements WebField {

    /**
     * @param Parameter $parameter
     * @return array With captions indexed by values
     */
    protected abstract function getOptions(Parameter $parameter);

    /**
     * @param Parameter $parameter
     * @param array $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        return $serialized;
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return $this->renderComboBox($parameter, $value);
    }

    protected function renderComboBox(Parameter $parameter, $value, $fieldNameSuffix = '', $options = []) {
        $id = str_replace(['[', ']'], '-', $parameter->getName());

        $optionsString = json_encode($options);
        $children = $this->renderOptions($parameter, (string)$value);
        $children[] = new Element('script', [], ["
              $(function(){
                $('#$id').combobox($optionsString);
              });
            "]);

        return (string)new Element('select', [
            'name' => $parameter->getName() . $fieldNameSuffix,
            'id' => $id,
            'class' => 'form-control combobox'
        ], $children);
    }

    private function renderOptions(Parameter $parameter, $value) {
        $options = [new Element('option', [], [])];
        foreach ($this->getOptions($parameter) as $key => $caption) {
            $options[] = new Element('option', array_merge([
                'value' => $key
            ], (string)$key === (string)$value ? [
                'selected' => 'selected'
            ] : []), [
                $caption
            ]);
        }
        return $options;
    }

    /**
     * @param Parameter $parameter
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function headElements(Parameter $parameter) {
        return [
            HeadElements::jquery(),
            HeadElements::style('//cdn.rawgit.com/rtens/bootstrap-combobox/cffe84e7/css/bootstrap-combobox.css'),
            HeadElements::script('//cdn.rawgit.com/rtens/bootstrap-combobox/cffe84e7/js/bootstrap-combobox.js'),
            new Element('style', [], ['.typeahead-long { width:100% }'])
        ];
    }
}