<?php

/**
 * Handle the request by finding the associated route from the routes list,
 * then invoke the target action of the target controller according the route parameters
 */
class Router
{
    /**
     * Holds the router singleton instance
     */
    private static $instance = null;

    /**
     * Holds the registered routes
     */
    private $routes = [];

    private function __construct()
    {
    }

    /**
     * Return router singleton instance
     * 
     * @return Router instance
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    /**
     * Register a route in the routes list
     * 
     * @param $route
     * @return void
     */
    public function addRoute(Route $route)
    {
        if (!$this->getRoute($route->actionUrl, $route->requestMethod)) {
            array_push($this->routes, $route);
        } else {
            error_log(sprintf(
                "A route with the same url and action is already registered, url: %s, method: %s",
                $route->actionUrl,
                $route->requestMethod
            ));
        }
    }

    /**
     * Get route from the routes list by the url and request method
     * 
     * @param $url
     * @param $requestMethod
     * @return Route|null
     */
    public function getRoute($url, $requestMethod)
    {
        $targetRoute = null;
        foreach ($this->routes as $route) {
            if ($route->actionUrl == $url && $route->requestMethod == $requestMethod) {
                $targetRoute = $route;
                break;
            }
        }
        return $targetRoute;
    }

    /**
     * Handle the request by invoknig the target action of the target controller
     * 
     * @return void
     */
    public function handleRequest()
    {
        $request = Request::getRequest();
        $route = $this->getRoute($request->actionUrl, $request->requestMethod);
        if ($route == null) {
            http_response_code(404);
        } else {
            $controller = new $route->controller;
            $controller->{$route->action}();
        }
    }
}
