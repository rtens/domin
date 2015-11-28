<?php
namespace rtens\domin\delivery\web\fields;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebField;
use rtens\domin\Parameter;
use watoki\reflect\type\ClassType;

class ActionField implements WebField {

    /** @var FieldRegistry */
    private $fields;

    /** @var ActionRegistry */
    private $actions;

    public function __construct(FieldRegistry $fields, ActionRegistry $actions) {
        $this->fields = $fields;
        $this->actions = $actions;
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        $type = $parameter->getType();
        return $type instanceof ClassType && is_subclass_of($type->getClass(), Action::class);
    }

    /**
     * @param Parameter $parameter
     * @param mixed[] $values
     * @return string
     * @throws \Exception
     */
    public function render(Parameter $parameter, $values) {
        $action = $this->actions->getAction($parameter->getName());
        $values = $action->fill($values);

        $body = [];

        if ($action->description()) {
            $body[] = new Element('div', ['class' => 'well'], [
                $action->description()
            ]);
        }

        foreach ($action->parameters() as $parameter) {
            $value = null;
            if (isset($values[$parameter->getName()])) {
                $value = $values[$parameter->getName()];
            }

            $body[] = new Element('div', ['class' => 'form-group'],
                $this->makeFormGroup($parameter, $value));
        }

        return $body ? (string)new Element('div', [], $body) : '';
    }

    private function makeFormGroup(Parameter $parameter, $value) {
        $formGroup = [
            new Element('label', [], [
                $this->makeLabel($parameter) . ($parameter->isRequired() ? '*' : '')
            ])
        ];

        if ($parameter->getDescription()) {
            $formGroup[] = new Element('a', ['class' => 'description'], [
                new Element('span', ['class' => 'glyphicon glyphicon-question-sign'])
            ]);
            $formGroup[] = new Element('div', ['class' => 'description-content sr-only'], [
                $parameter->getDescription()
            ]);
        }

        $field = $this->fields->getField($parameter);

        if (!($field instanceof WebField)) {
            throw new \Exception("[$parameter] is not a WebField");
        }

        $formGroup[] = $field->render($parameter, $value);
        return $formGroup;
    }

    /**
     * @param $parameter
     * @return mixed
     */
    protected function makeLabel(Parameter $parameter) {
        return ucfirst(preg_replace('/(.)([A-Z0-9])/', '$1 $2', $parameter->getName()));
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        return null;
    }

    /**
     * @param Parameter $parameter
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function headElements(Parameter $parameter) {
        $action = $this->actions->getAction($parameter->getName());

        $headElements = [
            self::descriptionCode()
        ];

        foreach ($action->parameters() as $parameter) {
            $field = $this->fields->getField($parameter);
            if ($field instanceof WebField) {
                $headElements = array_merge($headElements, $field->headElements($parameter));
            }
        }
        return $headElements;
    }

    private static function descriptionCode() {
        return new Element('script', [], ["
                $(function () {
                    $('.description').popover({
                        trigger: 'hover',
                        delay: {show: 100, hide: 1000},
                        html: true,
                        placement: 'auto top',
                        content: function () {
                            return $(this).siblings('.description-content').html();
                        }
                    });
                });"
        ]);
    }
}