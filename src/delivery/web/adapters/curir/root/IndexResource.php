<?php
namespace rtens\domin\delivery\web\adapters\curir\root;

use rtens\domin\delivery\web\adapters\curir\CurirBreadCrumbsTrail;
use rtens\domin\delivery\web\adapters\curir\CurirParameterReader;
use rtens\domin\delivery\web\resources\ActionListResource;
use rtens\domin\delivery\web\WebApplication;
use watoki\curir\Container;
use watoki\curir\cookie\CookieStore;
use watoki\curir\delivery\WebRequest;
use watoki\deli\Path;
use watoki\deli\Request;

class IndexResource extends Container {

    /**
     * @param Request|WebRequest $request
     * @return \watoki\curir\delivery\WebResponse
     */
    public function respond(Request $request) {
        if (!$this->isContainerTarget($request)) {
            $request = $request
                ->withTarget(Path::fromString('execute'))
                ->withArgument(ExecuteResource::ACTION_ARG, $request->getTarget()->toString());
        }
        return parent::respond($request);
    }

    /**
     * @param WebRequest $request <-
     * @param WebApplication $app <-
     * @param CookieStore $cookies <-
     * @return string
     */
    public function doGet(WebRequest $request, WebApplication $app, CookieStore $cookies) {
        $crumbs = new CurirBreadCrumbsTrail(new CurirParameterReader($request), $cookies);
        $actionList = new ActionListResource($app, $crumbs);

        return $actionList->handleGet();
    }
}