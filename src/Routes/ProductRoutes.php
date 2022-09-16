<?php

/**
 * Register the product controller action routes in the router singleton object
 */

require_once ROOT_PATH . "/src/Core/Router.php";
require_once ROOT_PATH . "/src/Core/Route.php";
require_once ROOT_PATH . "/src/Core/Request.php";

$router = Router::getInstance();

$router->addRoute(new Route(
    $actionUrl = "/product/importProducts",
    $controller = "ProductController",
    $action = "importProducts",
    $requestMethod = Request::POST
));

$router->addRoute(new Route(
    $actionUrl = "/product/getProducts",
    $controller = "ProductController",
    $action = "getProducts",
    $requestMethod = Request::GET
));

$router->addRoute(new Route(
    $actionUrl = "/product/updateProductsQuantity",
    $controller = "ProductController",
    $action = "updateProductsQuantity",
    $requestMethod = Request::GET
));
