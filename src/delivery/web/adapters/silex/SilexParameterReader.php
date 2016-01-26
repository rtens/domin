<?php
namespace rtens\domin\delivery\web\adapters\silex;

use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class SilexParameterReader implements ParameterReader {

    /** @var Request */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * IMPORTANT: files must be properly merged into the parameters
     *
     * @param Parameter $parameter
     * @return mixed The serialized paramater
     */
    public function read(Parameter $parameter) {
        if ($this->request->files->has($parameter->getName())) {
            return $this->map($this->request->files->get($parameter->getName()));
        }
        return $this->map($this->request->get($parameter->getName()));
    }

    /**
     * @param Parameter $parameter
     * @return boolean
     */
    public function has(Parameter $parameter) {
        return $this->request->files->has($parameter->getName())
        || $this->request->get($parameter->getName(), '__NOPEDINOPE__') != '__NOPEDINOPE__';
    }

    private function map($value) {
        if (is_array($value)) {
            $mapped = [];
            foreach ($value as $key => $item) {
                $mapped[$key] = $this->map($item);
            }
            return $mapped;
        } else if ($value instanceof UploadedFile) {
            return [
                'name' => $value->getClientOriginalName(),
                'tmp_name' => $value->getPath() . DIRECTORY_SEPARATOR . $value->getFilename(),
                'error' => $value->getError(),
                'size' => $value->getSize(),
                'type' => $value->getType()
            ];
        } else {
            return $value;
        }
    }
}