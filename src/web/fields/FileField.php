<?php
namespace rtens\domin\web\fields;

use rtens\domin\files\File;
use rtens\domin\files\SavedFile;
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
     * @param UploadedFile|null $serialized
     * @return \rtens\domin\files\File|null
     */
    public function inflate($serialized) {
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
            $attributes["required"] = 'true';
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