<?php namespace rtens\domin\delivery\cli\fields;

use rtens\domin\delivery\cli\CliField;
use rtens\domin\Parameter;
use watoki\reflect\type\ClassType;

class DateIntervalField implements CliField {

    /**
     * @param Parameter $parameter
     * @return null|string
     */
    public function getDescription(Parameter $parameter) {
        return "[d['d']] hh:mm (eg '3d 12:42')";
    }

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(\DateInterval::class);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return \DateInterval
     */
    public function inflate(Parameter $parameter, $serialized) {
        if (!$serialized) {
            return null;
        }

        $days = 0;
        if (strpos($serialized, ' ')) {
            list($days, $serialized) = explode(' ', $serialized);
        }
        list($hours, $minutes) = explode(':', $serialized);

        $days = intval(rtrim($days, "d"));
        $hours = intval($hours);
        $minutes = intval($minutes);

        return new \DateInterval("P{$days}DT{$hours}H{$minutes}M");
    }
}