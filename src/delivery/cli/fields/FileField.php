<?php
namespace rtens\domin\delivery\cli\fields;

use rtens\domin\delivery\cli\CliField;
use rtens\domin\Parameter;
use rtens\domin\parameters\File;
use rtens\domin\parameters\file\MemoryFile;
use rtens\domin\parameters\file\SavedFile;
use watoki\reflect\type\ClassType;

class FileField implements CliField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(File::class);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return File
     * @throws \Exception
     */
    public function inflate(Parameter $parameter, $serialized) {
        if (!is_file($serialized)) {
            return new MemoryFile('unknown', 'unknown');
        }
        return new SavedFile($serialized, basename($serialized), 'file');
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
    }
}