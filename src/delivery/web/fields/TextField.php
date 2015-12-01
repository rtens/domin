<?php namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebField;
use rtens\domin\Parameter;
use rtens\domin\parameters\Text;
use watoki\reflect\type\ClassType;

class TextField implements WebField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(Text::class);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        return new Text($serialized);
    }

    /**
     * @param Parameter $parameter
     * @param Text $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return (string)new Element('textarea', array_merge([
            'name' => $parameter->getName(),
            'class' => 'form-control',
        ], $parameter->isRequired() ? [
            'required' => 'required'
        ] : []), [
            $value ? $value->getContent() : null
        ]);
    }

    /**
     * @param Parameter $parameter
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }
}