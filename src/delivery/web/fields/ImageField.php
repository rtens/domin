<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\Parameter;
use rtens\domin\parameters\Image;
use rtens\domin\parameters\file\MemoryFile;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use watoki\reflect\type\ClassType;

class ImageField extends FileField {

    /**
     * For available options see https://github.com/fengyuanchen/cropper/#options
     * @return array
     */
    protected function getOptions() {
        return [
            'autoCropArea' => 1,
            'minContainerHeight' => 400,
            'guides' => false,
            'strict' => false
        ];
    }

    /**
     * @return array|array[] List of [nominator,denominator] pairs
     */
    protected function getAspectRatios() {
        return [
            [16, 9],
            [4, 3],
            [1, 1],
            [2, 3]
        ];
    }

    /**
     * @return array|int[]
     */
    protected function getRotatingAngles() {
        return [15, 90];
    }

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
                    ], $parameter->isRequired() && is_null($value) ? [
                        'required' => 'required'
                    ] : []))
                ]),

                new Element("input", [
                    'type' => 'hidden',
                    'class' => "image-data",
                    'name' => $parameter->getName() . '[encoded]'
                ]),

                new Element('div', ['class' => 'image-container', 'style' => 'display: none;'], [
                    new Element('div', ['class' => 'form-group image-controls'], $this->renderControls()),
                    new Element('img', ['class' => 'image-placeholder']),
                ])
            ]);
    }

    /**
     * @return Element[]
     */
    protected function renderControls() {
        return [
            new Element('div', ['class' => 'pull-right'], $this->renderSizeControls()),
            new Element('div', ['class' => 'btn-group'], $this->renderRotationButtons()),
            new Element('div', ['class' => 'btn-group'], array_merge(
                $this->renderAspectRatioButtons(),
                [$this->renderButton('free', 'No aspect ratio', "$(this).setOption('setAspectRatio', 0)")]
            )),
        ];
    }

    /**
     * @return Element[]
     */
    protected function renderSizeControls() {
        return [
            new Element('div', ['class' => 'input-group pull-right', 'style' => 'width: 250px'], [
                new Element('input', ['class' => 'form-control image-width', 'type' => 'text', 'title' => 'Image width']),
                new Element('span', ['class' => 'input-group-addon'], ['&times;']),
                new Element('input', ['class' => 'form-control image-height', 'type' => 'text', 'title' => 'Image height']),
                new Element('span', ['class' => 'input-group-addon'], ['px'])
            ]),
            new Element('div', ['class' => 'btn-group'], [
                $this->renderIconButton('resize-small', 'Shrink image', "$(this).changeFactor(1/1.25);"),
                $this->renderIconButton('resize-full', 'Enlarge image', "$(this).changeFactor(1.25);"),
                '&nbsp;'
            ]),
        ];
    }

    private function renderAspectRatioButtons() {
        return array_map(function ($ratio) {
            list($nom, $den) = $ratio;
            return $this->renderButton("$nom:$den", "Fix aspect ratio to $nom:$den", "$(this).setOption('setAspectRatio', $nom/$den)");
        }, $this->getAspectRatios());
    }

    private function renderRotationButtons() {
        return array_map(function ($angle) {
            return $this->renderButton("$angle&deg;", "Rotate by $angle degree", "$(this).setOption('rotate', $angle)");
        }, $this->getRotatingAngles());
    }

    protected function renderIconButton($glyphIcon, $title, $onClick) {
        return $this->renderButton(new Element('span', ['class' => 'glyphicon glyphicon-' . $glyphIcon]), $title, $onClick);
    }

    protected function renderButton($caption, $title, $onClick) {
        return new Element('span', [
            'class' => 'btn btn-default',
            'title' => $title,
            'onclick' => $onClick
        ], [$caption]);
    }

    /**
     * @param Parameter $parameter
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function headElements(Parameter $parameter) {
        $script = str_replace(
            '$cropperOptions$',
            json_encode($this->getOptions()),
            file_get_contents(__DIR__ . '/js/ImageField.js')
        );

        return [
            HeadElements::jquery(),
            HeadElements::bootstrapJs(),
            HeadElements::bootstrap(),
            HeadElements::script('//cdnjs.cloudflare.com/ajax/libs/cropper/0.9.3/cropper.min.js'),
            HeadElements::style('//cdnjs.cloudflare.com/ajax/libs/cropper/0.9.3/cropper.min.css'),
            new Element('script', [], [$script]),
        ];
    }
}