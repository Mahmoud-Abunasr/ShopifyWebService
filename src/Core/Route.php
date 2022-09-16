<?php

/**
 * Holds the required data for registering the action route in the router
 */
class Route
{
    /**
     * Holds the action url
     */
    public $actionUrl;

    /**
     * Holds the controller name
     */
    public $controller;

    /**
     * Holds the action function name in the controller
     */
    public $action;

    /**
     * Holds the allowed request method
     */
    public $requestMethod;

    public function __construct($actionUrl, $controller, $action, $requestMethod)
    {
        $this->actionUrl = $actionUrl;
        $this->controller = $controller;
        $this->action = $action;
        $this->requestMethod = $requestMethod;
    }
}
