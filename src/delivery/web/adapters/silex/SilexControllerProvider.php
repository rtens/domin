<?php
namespace rtens\domin\delivery\web\adapters\silex;

use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\web\BreadCrumbsTrail;
use rtens\domin\delivery\web\resources\ActionListResource;
use rtens\domin\delivery\web\resources\ExecutionResource;
use rtens\domin\delivery\web\WebApplication;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use watoki\factory\Factory;

class SilexControllerProvider implements ControllerProviderInterface {

    /** @var WebApplication */
    private $domin;

    public function __construct(Factory $factory) {
        $this->domin = $factory->getInstance(WebApplication::class);
    }

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app) {
        /** @var ControllerCollection $controller */
        $controller = $app['controllers_factory'];

        $controller->get('/', function (Request $request) {
            return $this->respond($request, function (BreadCrumbsTrail $crumbs) {
                $actionList = new ActionListResource($this->domin, $crumbs);
                return $actionList->handleGet();
            });
        });
        $controller->get('/{action}', function ($action, Request $request) {
            return $this->respond($request, function (BreadCrumbsTrail $crumbs, ParameterReader $reader) use ($action, $request) {
                $execution = new ExecutionResource($this->domin, $reader, $crumbs);
                return $execution->handleGet($action);
            });
        });
        $controller->post('/{action}', function ($action, Request $request) {
            return $this->respond($request, function (BreadCrumbsTrail $crumbs, ParameterReader $reader) use ($action, $request) {
                $execution = new ExecutionResource($this->domin, $reader, $crumbs);
                return $execution->handlePost($action);
            });
        });

        return $controller;
    }

    private function respond(Request $request, callable $content) {
        $reader = new SilexParameterReader($request);
        $crumbs = new SilexBreadCrumbsTrail($reader, $request);
        return $this->createResponse(call_user_func($content, $crumbs, $reader), $crumbs);
    }

    private function createResponse($content, SilexBreadCrumbsTrail $crumbs) {
        $response = Response::create($content);
        $response->headers->setCookie($crumbs->getCookie());
        return $response;
    }
}