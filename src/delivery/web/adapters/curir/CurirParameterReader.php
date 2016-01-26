<?php
namespace rtens\domin\delivery\web\adapters\curir;

use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;
use watoki\collections\Collection;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\UploadedFile;

class CurirParameterReader implements ParameterReader {

    /** @var WebRequest */
    private $request;

    public function __construct(WebRequest $request) {
        $this->request = $request;
    }

    /**
     * IMPORTANT: files must be properly merged into the parameters
     *
     * @param Parameter $parameter
     * @return mixed The serialized paramater
     */
    public function read(Parameter $parameter) {
        return $this->map($this->request->getArguments()->get($parameter->getName()));
    }

    /**
     * @param Parameter $parameter
     * @return boolean
     */
    public function has(Parameter $parameter) {
        return $this->request->getArguments()->has($parameter->getName());
    }

    private function map($value) {
        if (is_array($value) || $value instanceof Collection) {
            $mapped = [];
            foreach ($value as $key => $item) {
                $mapped[$key] = $this->map($item);
            }
            return $mapped;
        } else if ($value instanceof UploadedFile) {
            return [
                'name' => $value->getName(),
                'tmp_name' => $value->getTemporaryName(),
                'error' => $value->getError(),
                'size' => $value->getSize(),
                'type' => $value->getType()
            ];
        } else {
            return $value;
        }
    }
}