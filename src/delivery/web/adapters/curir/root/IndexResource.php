<?php
namespace rtens\domin\delivery\web\adapters\curir\root;

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
}