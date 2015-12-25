<?php
namespace rtens\domin\delivery\web\renderers\link;

interface Link {

    /**
     * @param object $object
     * @return boolean
     */
    public function handles($object);

    /**
     * @return string
     */
    public function actionId();

    /**
     * @param object $object
     * @return array|mixed[] Values indexed by parameter names
     */
    public function parameters($object);

    /**
     * A message that needs to be confirmed before the action can be executed (or null if not required)
     * @return string|null
     */
    public function confirm();

    /**
     * @return boolean Whether to force execution
     */
    public function force();
}