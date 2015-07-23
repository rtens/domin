<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\Parameter;
use rtens\domin\parameters\Html;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebField;
use watoki\reflect\type\ClassType;

class HtmlField implements WebField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(Html::class);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return Html
     */
    public function inflate(Parameter $parameter, $serialized) {
        return new Html($serialized);
    }

    /**
     * @param Parameter $parameter
     * @param Html|null $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $attributes = [
            'name' => $parameter->getName(),
            'class' => 'summernote',
        ];

        if ($parameter->isRequired()) {
            $attributes['required'] = 'required';
        }

        return (string)new Element('textarea', $attributes, [
            $value ? $value->getContent() : null
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
            HeadElements::fontAwesome(),
            HeadElements::style('//cdnjs.cloudflare.com/ajax/libs/summernote/0.6.10/summernote.min.css'),
            HeadElements::script('//cdnjs.cloudflare.com/ajax/libs/summernote/0.6.10/summernote.min.js'),
            new Element('script', [], ["
                $(document).ready(function() {
                    $('.summernote').summernote({
                        onKeyup: function(e) {
                            $(this).val($(this).code());
                        }
                    });
                });
            "])
        ];
    }
}