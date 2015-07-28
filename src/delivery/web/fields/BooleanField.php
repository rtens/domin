<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\web\HeadElements;
use rtens\domin\Parameter;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebField;
use watoki\reflect\type\BooleanType;

class BooleanField implements WebField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new BooleanType();
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        return !!$serialized;
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return implode('', [
            new Element('input', [
                'type' => 'hidden',
                'name' => $parameter->getName(),
                'value' => 0
            ]),
            new Element('input', array_merge([
                'class' => 'boolean-switch',
                'type' => 'checkbox',
                'name' => $parameter->getName(),
                'value' => 1,
            ], $value ? [
                'checked' => 'checked'
            ] : []))
        ]);
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        return [
            HeadElements::bootstrap(),
            HeadElements::jquery(),
            HeadElements::style('//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.2/css/bootstrap3/bootstrap-switch.min.css'),
            HeadElements::script('//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.2/js/bootstrap-switch.min.js'),
            new Element('script', [], ["
                $(function () {
                    $('.boolean-switch').bootstrapSwitch({
                        size: 'small',
                        onColor: 'success',
                        onText: 'Yes',
                        offText: 'No'
                    });
                });"])
        ];
    }
}