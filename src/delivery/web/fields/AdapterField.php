<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebField;
use rtens\domin\Parameter;

class AdapterField implements WebField {

    /** @var WebField */
    private $field;

    /** @var callable */
    private $handles;

    /** @var callable */
    private $afterInflate;

    /** @var callable */
    private $beforeRender;

    /** @var callable */
    private $afterHeadElements;

    /** @var callable */
    private $transformParameter;

    /**
     * @param WebField $field
     */
    public function __construct(WebField $field) {
        $this->field = $field;

        $this->handles = function (Parameter $parameter) {
            return $this->field->handles($parameter);
        };
        $this->beforeRender = function ($value) {
            return $value;
        };
        $this->afterInflate = function ($value) {
            return $value;
        };
        $this->afterHeadElements = function ($elements) {
            return $elements;
        };
        $this->transformParameter = function ($parameter) {
            return $parameter;
        };
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return call_user_func($this->handles, $parameter);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        $parameter = call_user_func($this->transformParameter, $parameter);
        $inflated = $this->field->inflate($parameter, $serialized);

        return call_user_func($this->afterInflate, $inflated);
    }

    /**
     * @param Parameter $parameter
     * @param string $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $parameter = call_user_func($this->transformParameter, $parameter);
        $value = call_user_func($this->beforeRender, $value);

        return $this->field->render($parameter, $value);
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        $parameter = call_user_func($this->transformParameter, $parameter);
        $elements = $this->field->headElements($parameter);

        return call_user_func($this->afterHeadElements, $elements);
    }

    /**
     * @param callable $handles
     * @return AdapterField
     */
    public function setHandles($handles) {
        $this->handles = $handles;
        return $this;
    }

    /**
     * @param string $name
     * @return static
     */
    public function setHandlesParameterName($name) {
        $this->setHandles(function (Parameter $parameter) use ($name) {
            return $parameter->getName() == $name;
        });
        return $this;
    }

    /**
     * @param callable $afterInflate
     * @return AdapterField
     */
    public function setAfterInflate($afterInflate) {
        $this->afterInflate = $afterInflate;
        return $this;
    }

    /**
     * @param callable $beforeRender
     * @return AdapterField
     */
    public function setBeforeRender($beforeRender) {
        $this->beforeRender = $beforeRender;
        return $this;
    }

    /**
     * @param callable $transformParameter
     * @return AdapterField
     */
    public function setTransformParameter($transformParameter) {
        $this->transformParameter = $transformParameter;
        return $this;
    }

    /**
     * @param callable $afterHeadElements
     * @return AdapterField
     */
    public function setAfterHeadElements($afterHeadElements) {
        $this->afterHeadElements = $afterHeadElements;
        return $this;
    }
}