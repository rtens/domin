<?php
namespace rtens\domin\delivery\web\adapters\curir\root;

use rtens\domin\delivery\web\adapters\curir\CurirBreadCrumbsTrail;
use rtens\domin\delivery\web\adapters\curir\CurirParameterReader;
use rtens\domin\delivery\web\resources\ExecutionResource;
use rtens\domin\delivery\web\WebApplication;
use watoki\curir\cookie\CookieStore;
use watoki\curir\delivery\WebRequest;
use watoki\curir\Resource;

class ExecuteResource extends Resource {

    const ACTION_ARG = '__action';

    /**
     * @param WebApplication $app <-
     * @param CookieStore $cookies <-
     * @param WebRequest $request <-
     * @param string $__action
     * @param bool $__force
     * @return string
     */
    public function doGet(WebApplication $app, CookieStore $cookies, WebRequest $request, $__action, $__force = false) {
        $execution = $this->getExecutionResource($app, $cookies, $request);
        return $execution->handleGet($__action, $__force);
    }

    /**
     * @param WebApplication $app <-
     * @param CookieStore $cookies <-
     * @param WebRequest $request <-
     * @param string $__action
     * @return string
     */
    public function doPost(WebApplication $app, CookieStore $cookies, WebRequest $request, $__action) {
        $execution = $this->getExecutionResource($app, $cookies, $request);
        return $execution->handlePost($__action);
    }

    private function getExecutionResource(WebApplication $app, CookieStore $cookies, WebRequest $request) {
        $app->prepare();

        $reader = new CurirParameterReader($request);
        return new ExecutionResource($app, $reader, new CurirBreadCrumbsTrail($reader, $cookies));
    }
}