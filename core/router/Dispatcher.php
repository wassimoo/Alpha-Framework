<?php
/**
 * Created by PhpStorm.
 * User: wassim
 * Date: 27/02/18
 * Time: 17:32
 */

class Dispatcher
{
    /**
     * @return String
     */
    public static function dispatch(){
        $parsedUrl = self::parseUrl($_SERVER['REQUEST_URI']);
        return $parsedUrl;
    }

    /**
     * process url and return parsed addresses
     * @param String $url
     * @return String of target path
     */
    private static function parseUrl(String $url)
    {
        $parsedUrl = parse_url($url);
        $parsedUrl["path"] = ltrim($parsedUrl["path"], "/"); /* start from left */
        $parsedUrl["path"] = trim($parsedUrl["path"]); // Remove from beginning and end
        $parsedUrl["path"] = strtolower($parsedUrl["path"]);
        return $parsedUrl["path"];
    }
}