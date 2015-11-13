<?php
namespace rtens\domin\delivery\web\renderers\link\types;

use rtens\domin\parameters\Identifier;

class IdentifierLink extends GenericLink {

    /**
     * @param string $target
     * @param string $actionId
     * @param string $identifierKey
     */
    public function __construct($target, $actionId, $identifierKey) {
        parent::__construct($actionId, function ($object) {
            return $object instanceof Identifier;
        }, function (Identifier $object) use ($identifierKey) {
            return [$identifierKey => $object->getId()];
        });
    }

}