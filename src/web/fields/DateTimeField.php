<?php
namespace rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\HeadElements;
use rtens\domin\web\WebField;
use watoki\reflect\type\ClassType;

class DateTimeField implements WebField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        $type = $parameter->getType();
        return $type instanceof ClassType && is_subclass_of($type->getClass(), \DateTimeInterface::class);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return \DateTimeImmutable
     */
    public function inflate(Parameter $parameter, $serialized) {
        return $serialized ? new \DateTimeImmutable($serialized) : null;
    }

    /**
     * @param Parameter $parameter
     * @param null|\DateTimeInterface $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return (string)new Element('div', [
            'class' => 'input-group date datetimepicker',
            'style' => 'width: 100%;'
        ], [
            new Element('span', [
                'class' => 'input-group-addon',
                'onclick' => "$(this).parents('.datetimepicker').datetimepicker(); $(this).siblings('.hidden').toggleClass('hidden'); $(this).remove(); return false;"
            ], [
                new Element('span', ['class' => 'glyphicon glyphicon-calendar', 'style' => 'opacity: 0.5'])
            ]),
            new Element('span', ['class' => 'input-group-addon hidden'], [
                new Element('span', ['class' => 'glyphicon glyphicon-calendar'])
            ]),
            new Element('input', [
                'type' => 'text',
                'name' => $parameter->getName(),
                'class' => 'form-control',
                'value' => $value ? $value->format('Y-m-d H:i:s') : null
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
            HeadElements::bootstrap(),
            HeadElements::bootstrapJs(),
            new Element('script', ['src' => '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.3/moment.min.js']),
            new Element('script', ['src' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.14.30/js/bootstrap-datetimepicker.min.js']),
            new Element('link', ['rel' => 'stylesheet', 'href' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.14.30/css/bootstrap-datetimepicker.min.css']),
            new Element('script', [], [
                'var dateTimePickerSettings = {
                    format: "dddd, D MMMM YYYY, HH:mm:ss",
                    extraFormats: ["YYYY-MM-DD HH:mm:ss"],
                    showTodayButton: true,
                    showClear: true
                };'
            ])
        ];
    }
}