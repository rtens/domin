<?php namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\cli\renderers\DateIntervalRenderer;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebField;
use \rtens\domin\delivery\cli\fields\DateIntervalField as CliDateIntervalField;
use rtens\domin\Parameter;

class DateIntervalField extends CliDateIntervalField implements WebField {

    /**
     * @param Parameter $parameter
     * @param \DateInterval $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $renderer = new DateIntervalRenderer();

        return (string)new Element('input', array_merge([
            'class' => 'form-control',
            'type' => 'text',
            'name' => $parameter->getName(),
            'value' => $renderer->render($value),
            'placeholder' => $this->getDescription($parameter)
        ], $parameter->isRequired() ? [
            'required' => 'required'
        ] : []));
    }

    /**
     * @param Parameter $parameter
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }
}