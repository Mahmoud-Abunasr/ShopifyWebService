<?php

/**
 * Load the application configuration from the json config files 
 * according the selected application run environment type
 */
class Config
{

    public const ENV_DEVELOPMENT = "dev";
    public const ENV_PRODUCTION = "prod";

    private static $config = null;

    private function __construct()
    {
    }

    /**
     * Load the application configuration according the run environment type
     * 
     * @return Config instnace
     */
    public static function getConfig()
    {
        if (self::$config == null) {
            $env = self::ENV_DEVELOPMENT;
            if (APP_ENV != null)
                $env = APP_ENV;
            $configStr = file_get_contents(ROOT_PATH . "/config/config." . $env . ".json");
            self::$config = json_decode($configStr, true);
        }

        return self::$config;
    }
}
