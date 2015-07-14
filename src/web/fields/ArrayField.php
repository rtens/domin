<?php
namespace rtens\domin\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\web\Element;
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
     * @param Liste $serialized
     * @return array
     */
    public function inflate($serialized) {
        return array_slice($serialized->toArray(), 0, -1);
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        /** @var ArrayType $type */
        $type = $parameter->getType();
        $innerParameter = new Parameter($parameter->getName() . '[]', $type->getItemType());

        /** @var WebField $innerField */
        $innerField = $this->fields->getField($innerParameter);

        $id = $parameter->getName();

        $items = [];
        foreach ($value as $item) {
            $items[] = $this->makeInputGroup($innerField, $innerParameter, $item);
        }

        return (string)new Element('div', [], [
            new Element('div', [
                'id' => "$id-container"
            ], $items),

            new Element('button', [
                'class' => 'btn btn-success',
                'onclick' => "document.getElementById('$id-container').appendChild(document.getElementById('$id-inner').getElementsByTagName('p')[0].cloneNode(true)); return false;"
            ], ['Add']),

            new Element('div', [
                'id' => "$id-inner",
                'class' => 'hidden'
            ], [
                $this->makeInputGroup($innerField, $innerParameter, null)
            ])
        ]);
    }

    private function makeInputGroup(WebField $field, Parameter $parameter, $value) {
        return new Element('p', [
            'class' => 'input-group'
        ], [
            $field->render($parameter, $value),
            new Element('span', [
                'class' => 'input-group-btn'
            ], [
                new Element('button', [
                    'class' => 'btn btn-danger',
                    'onclick' => 'var me = this.parentElement.parentElement; console.log(me.parentElement.removeChild(me)); return false;'
                ], ['X'])
            ])
        ]);
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }
}