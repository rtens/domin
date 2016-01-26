<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\FileRenderer;
use rtens\domin\delivery\web\WebField;
use rtens\domin\Parameter;
use rtens\domin\parameters\File;
use rtens\domin\parameters\file\MemoryFile;
use rtens\domin\parameters\file\SavedFile;
use watoki\reflect\type\ClassType;

class FileField implements WebField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(File::class);
    }

    /**
     * @param Parameter $parameter
     * @param array[]|string[] $serialized
     * @return null|File
     */
    public function inflate(Parameter $parameter, $serialized) {
        $file = $serialized['file'];

        if ($file && !$file['error']) {
            return new SavedFile($file['tmp_name'], $file['name'], $file['type']);
        } else if ($serialized['name']) {
            return $this->createPreservedFile($serialized);
        } else {
            return null;
        }
    }

    /**
     * @param string[] $serialized
     * @return File
     */
    protected function createPreservedFile($serialized) {
        return new MemoryFile($serialized['name'], $serialized['type'], base64_decode($serialized['data']));
    }

    /**
     * @param Parameter $parameter
     * @param File|null $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return (string)new Element('div', [], [
            $this->renderImagePreservation($parameter, $value),
            new ELement('label', [], [
                new Element('div', ['class' => 'input-group file-field'], [
                    new Element('span', ['class' => 'input-group-btn'], [
                        new Element('span', ['class' => 'btn btn-success'], ['Choose File']),
                        new Element("input", array_merge([
                            'class' => 'sr-only file-input',
                            'type' => 'file',
                            'name' => $parameter->getName() . '[file]'
                        ], $parameter->isRequired() && is_null($value) ? [
                            'required' => 'required'
                        ] : []))
                    ]),
                    new Element('span', ['class' => 'form-control file-name'])
                ])
            ])
        ]);
    }

    /**
     * @param Parameter $parameter
     * @param File|null $file
     * @return string
     */
    protected function renderImagePreservation(Parameter $parameter, File $file = null) {
        if ($file === null) {
            return '';
        }

        return (string)new Element('p', [], [
            new Element('input', [
                'type' => 'hidden',
                'name' => $parameter->getName() . '[name]',
                'value' => $file->getName()
            ]),
            new Element('input', [
                'type' => 'hidden',
                'name' => $parameter->getName() . '[type]',
                'value' => $file->getType()
            ]),
            new Element('input', [
                'type' => 'hidden',
                'name' => $parameter->getName() . '[data]',
                'value' => base64_encode($file->getContent())
            ]),
            (new FileRenderer())->render($file)
        ]);
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        return [
            new Element('script', [], ["
                $(function () {
                    $('.file-input').change(function (e) {
                        $(this).closest('.file-field').find('.file-name').html($(this)[0].files[0].name);
                    });
                });
            "])
        ];
    }
}