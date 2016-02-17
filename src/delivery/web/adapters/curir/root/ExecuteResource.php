<?php
namespace rtens\domin\delivery\web\adapters\curir\root;

use rtens\domin\delivery\web\adapters\curir\CurirBreadCrumbsTrail;
use rtens\domin\delivery\web\adapters\curir\CurirParameterReader;
use rtens\domin\delivery\web\resources\ExecutionResource;
use rtens\domin\delivery\web\WebApplication;
use watoki\curir\cookie\CookieStore;
use watoki\curir\delivery\WebRequest;
use watoki\curir\Resource;
use watoki\factory\Factory;

class ExecuteResource extends Resource {

    const ACTION_ARG = '__action';

    /** @var WebApplication */
    private $app;

    /** @var CookieStore */
    private $cookies;

    /**
     * @param Factory $factory <-
     * @param WebApplication $app <-
     * @param CookieStore $cookies <-
     */
    public function __construct(Factory $factory, WebApplication $app, CookieStore $cookies) {
        parent::__construct($factory);
        $this->app = $app;
        $this->cookies = $cookies;
    }

    /**
     * @param WebRequest $__request <-
     * @param string $__action
     * @param null|string $__token
     * @return string
     */
    public function doGet(WebRequest $__request, $__action, $__token = null) {
        $execution = $this->getExecutionResource($this->app, $this->cookies, $__request);
        return $execution->handleGet($__action, $__token);
    }

    /**
     * @param WebRequest $__request <-
     * @param string $__action
     * @param null|string $__token
     * @return string
     */
    public function doPost(WebRequest $__request, $__action, $__token = null) {
        $execution = $this->getExecutionResource($this->app, $this->cookies, $__request);
        return $execution->handlePost($__action, $__token);
    }

    private function getExecutionResource(WebApplication $app, CookieStore $cookies, WebRequest $request) {
        $reader = new CurirParameterReader($request);
        return new ExecutionResource($app, $reader, new CurirBreadCrumbsTrail($reader, $cookies));
    }
}