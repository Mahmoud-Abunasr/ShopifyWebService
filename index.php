<?php

/**
 * The application entry point, all the requests are redirected to this file, 
 * then handled by the Router class
 */

define("ROOT_PATH", __DIR__);
require_once ROOT_PATH . "/src/Core/Config.php";
require_once ROOT_PATH . "/src/Api/Controller/ProductController.php";
require_once ROOT_PATH . "/src/Routes/ProductRoutes.php";

/**
 * Set the application run environment type: [Config::ENV_DEVELOPMENT|Config::ENV_PRODUCTION]
 */
define("APP_ENV", Config::ENV_DEVELOPMENT);

/**
 * Handle the request in the Router class 
 * by invoking the target action of the target controller according the request parameters
 */
Router::getInstance()->handleRequest();
