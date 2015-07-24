<?php
namespace rtens\domin\delivery\web\fields;

use Detection\MobileDetect;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebField;
use watoki\collections\Liste;
use watoki\collections\Map;
use watoki\reflect\type\ArrayType;

class ArrayField implements WebField {

    const EMPTY_LIST_VALUE = '_____EMPTY_LIST_____';

    /** @var FieldRegistry */
    private $fields;

    /** @var MobileDetect */
    private $detect;

    public function __construct(FieldRegistry $fields, MobileDetect $detect) {
        $this->fields = $fields;
        $this->detect = $detect;
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
     * @param Liste|Map $serialized
     * @return array
     */
    public function inflate(Parameter $parameter, $serialized) {
        if ($serialized instanceof Map) {
            $serialized = $serialized->asList();
        }

        $itemParameter = $this->makeInnerParameter($parameter);

        return $serialized->slice(1)->map(function ($item) use ($itemParameter) {
            return $this->fields->getField($itemParameter)->inflate($itemParameter, $item);
        })->toArray();
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $id = str_replace(['[', ']'], '-', $parameter->getName());

        /** @var WebField $innerField */
        $innerField = $this->fields->getField($this->makeInnerParameter($parameter));

        $items = [
            new Element('input', [
                'type' => 'hidden',
                'name' => $parameter->getName() . '[0]',
                'value' => self::EMPTY_LIST_VALUE
            ])
        ];

        $index = 1;
        foreach ($value as $item) {
            $items[] = $this->makeInputGroup($innerField, $this->makeInnerParameter($parameter, '[' . $index++ . ']'), $id, $item);
        }

        $newItems = [];
        for ($i = 0; $i < $this->numberOfNewItems(); $i++) {
            $newItems[] = $this->makeInputGroup($innerField, $this->makeInnerParameter($parameter, '[' . $index++ . ']'), $id);
        }

        return (string)new Element('div', [], [
            new Element('div', [], [
                new Element('div', [
                    'id' => "$id-items",
                    'class' => 'array-items'
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
            new Element('span', ['class' => 'sortable-handle input-group-addon'], [
                new Element('span', ['class' => 'glyphicon glyphicon-sort'])
            ]),
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
        return array_merge([
            HeadElements::jquery(),
            HeadElements::jqueryUi(),
            new Element('script', [], [
                "$(function () {
                    $('.array-new-items').detach().appendTo('body');
                    $('.array-items').sortable({handle:'.sortable-handle'});
                    $('.array-items .sortable-handle').disableSelection();
                });"
            ])
        ], $this->isMobile() ? [
            HeadElements::script('//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js'),
        ] : [], $this->itemHeadElements($this->makeInnerParameter($parameter)));
    }

    private function itemHeadElements(Parameter $itemParameter) {
        /** @var WebField $field */
        $field = $this->fields->getField($itemParameter);
        return $field->headElements($itemParameter);
    }

    protected function numberOfNewItems() {
        return 30;
    }

    /**
     * @param Parameter $parameter
     * @param string $suffix
     * @return Parameter
     */
    private function makeInnerParameter(Parameter $parameter, $suffix = '') {
        /** @var ArrayType $type */
        $type = $parameter->getType();
        return new Parameter($parameter->getName() . $suffix, $type->getItemType());
    }

    private function isMobile() {
        return $this->detect->isMobile() || $this->detect->isTablet();
    }
}