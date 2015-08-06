<?php
namespace rtens\domin\delivery\cli\fields;

use rtens\domin\delivery\cli\CliField;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\Parameter;
use rtens\domin\parameters\Html;
use watoki\reflect\type\ClassType;

class HtmlField implements CliField {

    /** @var ParameterReader */
    private $reader;

    public function __construct(ParameterReader $reader) {
        $this->reader = $reader;
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(Html::class);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return Html
     */
    public function inflate(Parameter $parameter, $serialized) {
        $lines = [];

        $line = $serialized;
        while ($line) {
            $lines[] = $line;

            $lineParameter = $this->lineParameter($parameter, count($lines));
            $line = $this->reader->has($lineParameter) ? $this->reader->read($lineParameter) : null;
        }
        return new Html(implode(PHP_EOL, $lines));
    }

    /**
     * @param Parameter $parameter
     * @return string
     */
    public function getDescription(Parameter $parameter) {
    }

    private function lineParameter(Parameter $parameter, $lineNumber) {
        return new Parameter($parameter->getName() . '-' . $lineNumber, $parameter->getType());
    }
}