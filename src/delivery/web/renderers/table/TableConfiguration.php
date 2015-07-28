<?php
namespace rtens\domin\delivery\web\renderers\table;

use watoki\reflect\Property;

interface TableConfiguration {

    /**
     * @param object $object
     * @return boolean
     */
    public function appliesTo($object);

    /**
     * @param object $object
     * @return \watoki\reflect\Property[]
     */
    public function getProperties($object);

    /**
     * @param Property $property
     * @return string
     */
    public function getHeaderCaption(Property $property);

    /**
     * @param Property $property
     * @param object $object
     * @return mixed
     */
    public function getValue(Property $property, $object);
}