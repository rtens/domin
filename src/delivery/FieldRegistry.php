<?php
namespace rtens\domin\delivery;

class FieldRegistry {

    /** @var array|Field[] */
    private $fields = [];

    /**
     * @param string $type
     * @return Field
     * @throws \Exception
     */
    public function getField($type) {
        foreach ($this->fields as $field) {
            if ($field->handles($type)) {
                return $field;
            }
        }

        throw new \Exception("No field found to handle [$type]");
    }

    public function add(Field $field) {
        $this->fields[] = $field;
    }
} 