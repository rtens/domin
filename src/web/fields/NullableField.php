<?php
namespace rtens\domin\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\HeadElements;
use rtens\domin\web\WebField;
use watoki\reflect\type\NullableType;

class NullableField implements WebField {

    /** @var FieldRegistry */
    private $fields;

    /**
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
        return $parameter->getType() instanceof NullableType;
    }

    /**
     * @param Parameter $parameter
     * @param mixed $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        $innerParameter = $this->getInnerParameter($parameter);
        return $this->fields->getField($innerParameter)
            ->inflate($innerParameter, $serialized);
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $id = str_replace(['[', ']'], '-', $parameter->getName());

        return implode("\n", [
            new Element('input', array_merge([
                'type' => 'checkbox',
                'onchange' => "var control = $('#$id-control').detach(); $(this).is(':checked') ? control.show().insertAfter($(this)) : control.hide().appendTo('body');"
            ], is_null($value) ? [] : [
                'checked' => 'checked'
            ])),
            new Element('div', array_merge([
                'id' => "$id-control"
            ], is_null($value) ? [
                'class' => 'null-nullable'
            ] : []), [
                $this->getInnerField($this->getInnerParameter($parameter))
                    ->render($this->getInnerParameter($parameter), $value)
            ])
        ]);
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        $innerParameter = $this->getInnerParameter($parameter);
        return array_merge($this->getInnerField($innerParameter)->headElements($innerParameter), [
            HeadElements::jquery(),
            new Element('script', [], ["$(function () {
                    $('.null-nullable').hide().detach().appendTo('body');
                });"])
        ]);
    }

    private function getInnerParameter(Parameter $parameter) {
        $type = $parameter->getType();
        if (!($type instanceof NullableType)) {
            throw new \InvalidArgumentException("[$type] is not a NullableType");
        }

        return new Parameter($parameter->getName(), $type->getType());
    }

    /**
     * @param Parameter $innerParameter
     * @return WebField
     */
    private function getInnerField(Parameter $innerParameter) {
        return $this->fields->getField($innerParameter);
    }
}