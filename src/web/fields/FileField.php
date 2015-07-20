<?php
namespace rtens\domin\web\fields;

use rtens\domin\parameters\File;
use rtens\domin\parameters\MemoryFile;
use rtens\domin\parameters\SavedFile;
use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\renderers\FileRenderer;
use rtens\domin\web\WebField;
use watoki\collections\Map;
use watoki\curir\protocol\UploadedFile;
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
     * @param Map|UploadedFile[]|string[] $serialized
     * @return null|File
     */
    public function inflate(Parameter $parameter, $serialized) {
        $file = $serialized['file'];

        if (!$file->getError()) {
            return new SavedFile($file->getTemporaryName(), $file->getName(), $file->getType());
        } else if ($serialized['name']) {
            return new MemoryFile($serialized['name'], $serialized['type'], base64_decode($serialized['data']));
        } else {
            return null;
        }
    }

    /**
     * @param Parameter $parameter
     * @param File|null $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $attributes = [
            "type" => "file",
            "name" => $parameter->getName() . '[file]'
        ];

        if ($parameter->isRequired()) {
            $attributes["required"] = 'required';
        }

        $output = (string)new Element("input", $attributes);

        if ($value) {
            return new Element('p', [], [
                    new Element('input', [
                        'type' => 'hidden',
                        'name' => $parameter->getName() . '[name]',
                        'value' => $value->getName()
                    ]),
                    new Element('input', [
                        'type' => 'hidden',
                        'name' => $parameter->getName() . '[type]',
                        'value' => $value->getType()
                    ]),
                    new Element('input', [
                        'type' => 'hidden',
                        'name' => $parameter->getName() . '[data]',
                        'value' => base64_encode($value->getContent())
                    ]),
                    (new FileRenderer())->render($value)
                ]) . $output;
        }

        return $output;
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }
}