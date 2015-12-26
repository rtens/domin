<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\cli\fields\RangeField as CliRangeField;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\MobileDetector;
use rtens\domin\delivery\web\WebField;
use rtens\domin\Parameter;
use rtens\domin\reflection\types\RangeType;

class RangeField extends CliRangeField implements WebField {

    /** @var MobileDetector */
    private $mobile;

    public function __construct(MobileDetector $mobile) {
        $this->mobile = $mobile;
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        /** @var RangeType $range */
        $range = $parameter->getType();
        $min = $range->getMin();
        $max = $range->getMax();
        $step = $range->getStep();

        $value = (int)$value;
        $name = $parameter->getName();

        return (string)new Element('div', ['class' => 'form-group', 'id' => $name], [
            new Element('div', ['class' => 'slider']),
            new Element('input', [
                'class' => 'amount form-control',
                'type' => 'number',
                'name' => $name,
                'value' => $value
            ]),
            new Element('script', [], [
                "$(function() {
                    $('#$name .slider').slider({
                      range: 'min',
                      value: $value,
                      min: $min,
                      max: $max,
                      step: $step,
                      slide: function(event, ui) {
                            $('#$name .amount').val(ui.value);
                        }
                    });
                });"
            ])
        ]);
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        $elements = [
            HeadElements::jquery(),
            HeadElements::jqueryUi(),
            HeadElements::jqueryUiCss(),
        ];

        if ($this->mobile->isMobile()) {
            $elements[] = HeadElements::script('//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js');
        }

        return $elements;
    }
}