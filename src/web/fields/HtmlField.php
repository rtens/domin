<?php
namespace rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\parameters\Html;
use rtens\domin\web\Element;
use rtens\domin\web\HeadElements;
use rtens\domin\web\WebField;
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
     * @param string $serialized
     * @return Html
     */
    public function inflate($serialized) {
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
            new Element('link', ['rel' => 'stylesheet', 'href' => '//cdnjs.cloudflare.com/ajax/libs/summernote/0.6.10/summernote.min.css']),
            new Element('script', ['src' => '//cdnjs.cloudflare.com/ajax/libs/summernote/0.6.10/summernote.min.js'], ['']),
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