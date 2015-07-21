<?php
namespace rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\parameters\Image;
use rtens\domin\parameters\MemoryFile;
use rtens\domin\web\Element;
use rtens\domin\web\HeadElements;
use watoki\reflect\type\ClassType;

class ImageField extends FileField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(Image::class);
    }

    /**
     * @param Parameter $parameter
     * @param \watoki\collections\Map|string[] $serialized
     * @return \rtens\domin\parameters\Image
     */
    public function inflate(Parameter $parameter, $serialized) {
        if ($serialized['encoded']) {
            list($nameAndType, $data) = explode(';base64,', $serialized['encoded']);
            list($name, $type) = explode(';;data:', $nameAndType);

            return new Image(new MemoryFile($name, $type, base64_decode($data)));
        } else if ($serialized['name']) {
            return new Image($this->createPreservedFile($serialized));
        } else {
            return null;
        }
    }

    /**
     * @param Parameter $parameter
     * @param null|Image $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return
            $this->renderImagePreservation($parameter, $value ? $value->getFile() : null) .
            new Element('div', ['class' => 'image-cropper'], [
                new Element('label', [], [
                    new Element('span', ['class' => 'btn btn-success'], ['Choose Image']),
                    new Element("input", array_merge([
                        'class' => 'sr-only image-input',
                        "type" => "file",
                    ], $parameter->isRequired() ? [
                        'required' => 'required'
                    ] : []))
                ]),

                new Element("input", [
                    'type' => 'hidden',
                    'class' => "image-data",
                    'name' => $parameter->getName() . '[encoded]'
                ]),

                new Element('div', ['class' => 'image-container', 'style' => 'display: none;'], [
                    new Element('div', ['class' => 'form-group'], [
                        new Element('div', ['class' => 'input-group pull-right', 'style' => 'width: 250px'], [
                            new Element('input', ['class' => 'form-control image-width', 'type' => 'text']),
                            new Element('span', ['class' => 'input-group-addon'], ['x']),
                            new Element('input', ['class' => 'form-control image-height', 'type' => 'text']),
                            new Element('span', ['class' => 'input-group-addon'], ['px']),
                        ]),

                        new Element('div', ['class' => 'btn-group'], [
                            $this->renderIconButton('refresh', 'rotate', 15),
                            $this->renderIconButton('repeat', 'rotate', 90),
                        ]),
                        new Element('div', ['class' => 'btn-group'], [
                            $this->renderButton('16:9', 'setAspectRatio', '16/9'),
                            $this->renderButton('4:3', 'setAspectRatio', '4/3'),
                            $this->renderButton('1:1', 'setAspectRatio', '1'),
                            $this->renderButton('2:3', 'setAspectRatio', '2/3'),
                            $this->renderButton('free', 'setAspectRatio', '0'),
                        ]),
                    ]),
                    new Element('img', ['class' => 'image-placeholder']),
                ])
            ]);
    }

    private function renderIconButton($glyphIcon, $option, $value) {
        return $this->renderButton(new Element('span', ['class' => 'glyphicon glyphicon-' . $glyphIcon]), $option, $value);
    }

    private function renderButton($caption, $option, $value) {
        return new Element('span', [
            'class' => 'btn btn-default',
            'onclick' => "$(this).parents('.image-cropper').find('.image-placeholder').cropper('{$option}', {$value});"
        ], [$caption]);
    }

    /**
     * @param Parameter $parameter
     * @return array|\rtens\domin\web\Element[]
     */
    public function headElements(Parameter $parameter) {
        return [
            HeadElements::jquery(),
            HeadElements::bootstrapJs(),
            HeadElements::bootstrap(),
            HeadElements::script('//cdnjs.cloudflare.com/ajax/libs/cropper/0.9.3/cropper.min.js'),
            HeadElements::style('//cdnjs.cloudflare.com/ajax/libs/cropper/0.9.3/cropper.min.css'),
            new Element('script', [], [file_get_contents(__DIR__ . '/js/ImageField.js')]),
        ];
    }
}