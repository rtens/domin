<?php namespace rtens\domin\delivery\web;

use rtens\domin\AccessControl;
use watoki\curir\delivery\WebRequest;

class WebAccessControl implements AccessControl {

    /** @var WebRequest */
    protected $request;

    /** @var AccessControl */
    private $access;

    /** @var null|callable */
    private $acquire;

    /**
     * @param AccessControl $access
     * @param WebRequest $request
     * @param callable|null $acquire Receives the WebRequest, returns a URL to redirect to
     */
    public function __construct(AccessControl $access, WebRequest $request, callable $acquire = null) {
        $this->request = $request;
        $this->access = $access;
        $this->acquire = $acquire;
    }

    /**
     * @param AccessControl $access
     * @param callable|null $acquire Receives the WebRequest, returns a URL to redirect to
     * @return callable The factory to build a WebAccessControl
     */
    public static function factory(AccessControl $access, callable $acquire = null) {
        return function (WebRequest $request) use ($access, $acquire) {
            return new WebAccessControl($access, $request, $acquire);
        };
    }

    /**
     * @param string $actionId
     * @return boolean
     */
    public function isVisible($actionId) {
        return $this->access->isVisible($actionId);
    }

    /**
     * @param string $actionId
     * @param mixed[] $parameters indexed by parameter names
     * @return boolean
     */
    public function isExecutionPermitted($actionId, array $parameters) {
        return $this->access->isExecutionPermitted($actionId, $parameters);
    }

    /**
     * @return null|\watoki\curir\protocol\Url
     */
    public function acquirePermission() {
        if ($this->acquire) {
            return call_user_func($this->acquire, $this->request);
        }

        return null;
    }
}