<?php
namespace rtens\domin\delivery;

use rtens\domin\Parameter;

class FieldRegistry {

    /** @var array|Field[] */
    private $fields = [];

    /**
     * @param Parameter $parameter
     * @return Field
     * @throws \Exception
     */
    public function getField(Parameter $parameter) {
        foreach ($this->fields as $field) {
            if ($field->handles($parameter)) {
                return $field;
            }
        }

        throw new \Exception("No field found to handle [$parameter]");
    }

    public function add(Field $field) {
        $this->fields[] = $field;
    }
} 