<?php
namespace rtens\domin\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\HeadElements;
use rtens\domin\web\WebField;
use watoki\collections\Liste;
use watoki\reflect\type\ArrayType;

class ArrayField implements WebField {

    /** @var FieldRegistry */
    private $fields;

    function __construct(FieldRegistry $fields) {
        $this->fields = $fields;
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() instanceof ArrayType;
    }

    /**
     * @param Parameter $parameter
     * @param Liste $serialized
     * @return array
     */
    public function inflate(Parameter $parameter, $serialized) {
        return $serialized->toArray();
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $id = str_replace('[]', '-', $parameter->getName());

        /** @var ArrayType $type */
        $type = $parameter->getType();
        $innerParameter = new Parameter($parameter->getName() . '[]', $type->getItemType());

        /** @var WebField $innerField */
        $innerField = $this->fields->getField($innerParameter);

        $items = [];
        foreach ($value as $item) {
            $items[] = $this->makeInputGroup($innerField, $innerParameter, $id, $item);
        }

        $newItems = [];
        for ($i = 0; $i < $this->numberOfNewItems(); $i++) {
            $newItems[] = $this->makeInputGroup($innerField, $innerParameter, $id);
        }

        return (string)new Element('div', [], [
            new Element('div', [], [
                new Element('div', [
                    'id' => "$id-items"
                ], $items),

                new Element('button', [
                    'class' => 'btn btn-success',
                    'onclick' => "$('#$id-new-items').children().first().detach().appendTo('#$id-items'); return false;"
                ], ['Add']),

                new Element('div', [
                    'id' => "$id-new-items",
                    'class' => 'array-new-items hidden'
                ], $newItems)
            ])
        ]);
    }

    private function makeInputGroup(WebField $field, Parameter $parameter, $id, $value = null) {
        return new Element('div', [
            'class' => 'array-item form-group input-group'
        ], [
            $field->render($parameter, $value),
            new Element('span', [
                'class' => 'input-group-btn'
            ], [
                new Element('button', [
                    'class' => 'btn btn-danger',
                    'onclick' => "$(this).parents('.array-item').detach().prependTo('#$id-new-items'); return false;"
                ], ['X'])
            ])
        ]);
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        return [
            HeadElements::jquery(),
            new Element('script', [], [
                "$(function () {
                    $('.array-new-items').detach().appendTo('body');
                });"
            ])
        ];
    }

    protected function numberOfNewItems() {
        return 30;
    }
}