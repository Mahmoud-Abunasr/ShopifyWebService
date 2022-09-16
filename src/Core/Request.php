<?php

/**
 * Extract the required data from the request and store it in a singleton request object
 */
class Request
{
    /**
     * Request methods constants
     */
    public const GET = "get";
    public const POST = "post";
    public const PUT = "put";
    public const OPTION = "option";
    public const DELETE = "delete";

    /**
     * Holds the target action url
     */
    public $actionUrl;

    /**
     * Holds the request method
     */
    public $requestMethod;

    /**
     * Holds the request singleton instance
     */
    private static $request;

    private function __construct()
    {
        $this->getRequestInfo();
    }

    /**
     * Extract the required data from the request
     * 
     * @return void
     */
    private function getRequestInfo()
    {
        $rootDirPathParts = explode(DIRECTORY_SEPARATOR, ROOT_PATH);
        $rootDir = end($rootDirPathParts);
        $urlParts = explode("/", $_SERVER['REQUEST_URI']);
        $rootDirIndex = array_search($rootDir, $urlParts);
        if ($rootDirIndex === false) {
            $this->actionUrl = $_SERVER['REQUEST_URI'];
        } else {
            for ($index = ++$rootDirIndex; $index < sizeof($urlParts); $index++) {
                $this->actionUrl = $this->actionUrl . "/" . $urlParts[$index];
            }
        }
        $this->requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
    }

    /**
     * Return a request singleton instance
     * 
     * @return Request instance
     */
    public static function getRequest()
    {
        if (self::$request == null) {
            self::$request = new Request();
        }
        return self::$request;
    }

    /**
     * Get file info from the request if exists
     * 
     * @param $fileName
     * @return array|null
     */
    public function getFile($fileName)
    {
        $targetFile = null;
        if (array_key_exists($fileName, $_FILES)) {
            $targetFile = $_FILES[$fileName];
        }
        return $targetFile;
    }
}
