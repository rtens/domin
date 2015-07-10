<?php
namespace spec\rtens\domin\fixtures;

use rtens\scrut\Fixture;
use spec\watoki\deli\fixtures\TestDelivererStub;
use watoki\collections\Liste;
use watoki\collections\Map;
use watoki\curir\delivery\WebRequest;
use watoki\curir\delivery\WebResponse;
use watoki\curir\error\ErrorResponse;
use watoki\curir\protocol\Url;
use watoki\curir\WebDelivery;
use watoki\deli\Path;
use watoki\deli\router\NoneRouter;
use watoki\deli\target\RespondingTarget;
use watoki\factory\Factory;

class WebFixture extends Fixture {

    public $model;

    /** @var Factory */
    public $factory;

    /** @var WebRequest */
    public $request;

    public function before() {
        $this->request = new WebRequest(Url::fromString('http://example.com/base'), Path::fromString(''), 'get', new Map(), new Liste(['null']));
        $this->factory = WebDelivery::init();
    }

    public function whenIGet_From($path, $resourceClass) {
        $request = $this->request
            ->withTarget(Path::fromString($path))
            ->withMethod('get');

        $stub = new TestDelivererStub($request);
        $router = new NoneRouter(RespondingTarget::factory($this->factory, $this->factory->getInstance($resourceClass)));
        $delivery = new WebDelivery($router, $stub, $stub);

        $stub->onDeliver(function (WebResponse $response) {
            if ($response instanceof ErrorResponse) {
                throw $response->getException();
            }
            $this->model = $response->getBody();
        });

        $delivery->run();
    }
}