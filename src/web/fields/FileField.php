<?php
namespace rtens\domin\web\fields;

use rtens\domin\parameters\File;
use rtens\domin\parameters\SavedFile;
use rtens\domin\Parameter;
use rtens\domin\web\Element;
use rtens\domin\web\WebField;
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
     * @param UploadedFile|null $serialized
     * @return null|File
     */
    public function inflate(Parameter $parameter, $serialized) {
        if (!$serialized) {
            return null;
        }
        return new SavedFile($serialized->getTemporaryName(), $serialized->getName(), $serialized->getType());
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $attributes = [
            "type" => "file",
            "name" => $parameter->getName()
        ];

        if ($parameter->isRequired()) {
            $attributes["required"] = 'required';
        }

        return (string) new Element("input", $attributes);
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }
}