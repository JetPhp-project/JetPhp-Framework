<?php

namespace JetPhp\Core\Routing;

use JetPhp\Core\Helpers\SupportedHttpMethods;
use JetPhp\Core\Helpers\Utilities;
use JetPhp\Exeptions\ControllerException;
use JetPhp\Response\BaseResponse;
use Symfony\Component\Routing\Route;


/**
 * Changing how we define our routes in the app
 *
 * Now we need to do like this
 *
 * @example ```php
 * $routes = new JetRouter(someController, theItsApiHere);
 * ```
 */
class JetRouter
{
    protected $routes;

    private string | null $controller = null;
    private string $basePath = '/api/v1/';

    public function getRoutes(): BaseRoutes
    {
        return $this->routes;
    }

    public function __construct(BaseRoutes | null $routes = null)
    {
        $this->routes = $routes ?? new BaseRoutes();
    }

    private function resolveController(string |null $_controller = null, $setAsCurrent = true): static
    {
        $controller = $_controller ?? $this->controller;

        if (!$controller){
            throw new ControllerException('No controller defined');
        }

        $res = Utilities::extends($controller, 'JetPhp\Core\BaseApiController');
        if ($res ==='NO_CLASS'){
            throw new ControllerException("Controller {$controller} class not found");
        } elseif ($res === 'DOES_NOT') {
            throw new ControllerException("Controller {$controller} does not implement BaseApiController");
        }

        if ($setAsCurrent){
            $this->controller = $controller;
        }
        return $this;
    }


    public function post(string $action, string $name)
    {
        try {
            if (!$this->controller) {
                throw new ControllerException('No controller defined, Hint: Have you called addGroup yet?');
            }
            $this->addRoute( $action, $name);
            return $this;
        } catch (ControllerException $e) {
            return BaseResponse::JsonResponse(500, $e->getMessage());
        }
    }

    public function addGroup(string $controller, string $basePath = '/api/v1/'): static
    {
        $this->resolveController($controller);
        $this->basePath = $basePath;
        return $this;
    }


    public function get(string $action, string $name)
    {
        try {
            if (!$this->controller) {
                throw new ControllerException('No controller defined, Hint: Have you called addGroup yet?');
            }
            $this->addRoute( $action, $name,SupportedHttpMethods::GET);
            return $this;
        } catch (ControllerException $e) {
            return BaseResponse::JsonResponse(500, $e->getMessage());
        }
    }

    /**
     * @throws ControllerException
     */
    private function addRoute(string $action, string $name, string | array $method = SupportedHttpMethods::POST, $controller = null, $condition = ''){
        if ($controller){
            $this->resolveController($controller);
        }
        $methods = [];
        if (is_string($method)){
            $methods = [$method];
        } else if (is_array($method)){
            $methods = $method;
        }

        if (!method_exists($this->controller, $action)) {
            throw new ControllerException("Action {$action} does not exist in controller {$this->controller}");
        }

        $route = new Route($this->basePath, [
            '_controller' => $this->controller.'::'.$action,
        ], [], [],null, [], $methods, $condition);

        $this->routes->add($name, $route);
        return $this;
    }

}
