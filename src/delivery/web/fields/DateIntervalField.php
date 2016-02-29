<?php namespace rtens\domin\delivery\web\fields;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebField;
use \rtens\domin\delivery\cli\fields\DateIntervalField as CliDateIntervalField;
use rtens\domin\Parameter;

class DateIntervalField extends CliDateIntervalField implements WebField {

    /**
     * @param Parameter $parameter
     * @param \DateInterval $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return (string)new Element('div', ['class' => 'form-inline'], [
            new Element('input', [
                'class' => 'form-control',
                'type' => 'number',
                'size' => 3,
                'name' => $parameter->getName() . '[d]',
                'value' => $value ? $value->d : null]),
            new Element('span', [], ['days']),
            new Element('input', [
                'class' => 'form-control',
                'type' => 'number',
                'size' => 3,
                'name' => $parameter->getName() . '[h]',
                'value' => $value ? $value->h : null]),
            new Element('span', [], ['hours']),
            new Element('input', [
                'class' => 'form-control',
                'type' => 'number',
                'size' => 3,
                'name' => $parameter->getName() . '[i]',
                'value' => $value ? $value->i : null]),
            new Element('span', [], ['minutes']),
        ]);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return \DateInterval
     */
    public function inflate(Parameter $parameter, $serialized) {
        if (!$serialized) {
            return $parameter->isRequired() ? new \DateInterval('P0D') : null;
        }

        $days = isset($serialized['d']) ? intval($serialized['d']) : 0;
        $hours = isset($serialized['h']) ? intval($serialized['h']) : 0;
        $minutes = isset($serialized['i']) ? intval($serialized['i']) : 0;

        return new \DateInterval("P{$days}DT{$hours}H{$minutes}M");
    }

    /**
     * @param Parameter $parameter
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function headElements(Parameter $parameter) {
        return [];
    }
}