<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebField;
use rtens\domin\Parameter;
use watoki\reflect\Type;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\MultiType;

class MultiField implements WebField {

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
        return $parameter->getType() instanceof MultiType;
    }

    /**
     * @param Parameter $parameter
     * @param array $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        foreach ($this->getTypes($parameter) as $i => $type) {
            if (array_key_exists("multi-$i", $serialized)) {
                $optionParameter = new Parameter($parameter->getName(), $type);
                return $this->getField($optionParameter)->inflate($optionParameter, $serialized["multi-$i"]);
            }
        }
        return null;
    }

    /**
     * @param Parameter $parameter
     * @param mixed|null $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $id = str_replace(['[', ']'], '-', $parameter->getName());

        return implode("\n", array_merge([
            new Element('select', [
                'class' => 'form-control form-group',
                'onchange' => "$(this).next().hide().appendTo('body'); $('#' + $(this).val()).show().insertAfter($(this));"
            ], $this->getOptions($parameter, $value, $id))
        ], $this->renderOptions($parameter, $value, $id)));
    }

    private function renderOptions(Parameter $parameter, $value, $id) {
        $fields = [];
        foreach ($this->getTypes($parameter) as $i => $type) {
            $optionParameter = new Parameter($parameter->getName() . "[multi-$i]", $type);
            $selected = is_null($value) && $i == 0 || $type->is($value);
            $fields[] = new Element('div', ['class' => 'multi-control' . ($selected ? '' : ' not-selected'), 'id' => "$id-multi-option-$i"], [
                $this->getField($optionParameter)->render($optionParameter, $type->is($value) ? $value : null)
            ]);
        }
        return $fields;
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        $ownElements = [
            HeadElements::jquery(),
            new Element('script', [], [
                "$(function () {
                    $('.multi-control.not-selected').hide().appendTo('body');
                });"
            ])
        ];

        foreach ($this->getTypes($parameter) as $i => $type) {
            $optionParameter = new Parameter($parameter->getName(), $type);
            $optionElements = $this->getField($optionParameter)->headElements($optionParameter);
            $ownElements = array_merge($ownElements, $optionElements);
        }

        return $ownElements;
    }

    private function getOptions(Parameter $parameter, $value, $id) {
        $options = [];
        foreach ($this->getTypes($parameter) as $i => $type) {
            $options[] = new Element('option', array_merge([
                'value' => "$id-multi-option-$i"
            ], $type->is($value) ? [
                'selected' => 'selected'
            ] : []), [
                $this->toString($type)
            ]);
        }
        return $options;
    }

    private function toString(Type $type) {
        if ($type instanceof ClassType) {
            return (new \ReflectionClass($type->getClass()))->getShortName();
        } else {
            return (string)$type;
        }
    }

    private function getTypes(Parameter $parameter) {
        $type = $parameter->getType();
        if (!($type instanceof MultiType)) {
            throw new \InvalidArgumentException("[$type] must be a MultiType");
        }

        return $type->getTypes();
    }

    /**
     * @param Parameter $optionParameter
     * @return WebField
     */
    private function getField($optionParameter) {
        return $this->fields->getField($optionParameter);
    }
}