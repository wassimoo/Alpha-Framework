<?php
/**
 * Created by PhpStorm.
 * User: wassim
 * Date: 27/02/18
 * Time: 17:32
 */

namespace AlphaRouter;

class Dispatcher
{
    /**
     * @param string $prefix will be masked when parsing url;
     * @param string $pattern expected url pattern
     * @return associative-array pattern_param => value  + rest => value
     */
    public static function dispatch(String $prefix, string $pattern = "")
    {
        $parsedUrl = self::parseUrl(parse_url($_SERVER['REQUEST_URI'])["path"], $prefix);
        $parsedPattern = self::parseUrl($pattern, $prefix);
        return self::matchPattern($parsedUrl, $parsedPattern);
    }

    /**
     * process url and return parsed addresses
     * @param String $url
     * @return array $parsedUrl
     */
    private static function parseUrl(String $url, String $prefix)
    {
        $parsedUrl = trim($url); // Remove from beginning and end
        $parsedUrl = self::lmask($parsedUrl, "/$prefix"); /* start from left */
        $parsedUrl = self::rmask($parsedUrl);
        $parsedUrl = strtolower($parsedUrl);
        return explode("/", $parsedUrl);
    }

    /**
     * Mask string from begining of  $str
     * @param string $str string to be masked
     * @param string $suffix mask
     * @return string masked string
     */
    private static function lmask(String $str, $prefix = "")
    {
        if ($prefix !== "") {

            if (substr($str, 0, strlen($prefix)) == $prefix) {
                $str = substr($str, strlen($prefix));
            }
        }
        return ltrim($str, "/");
    }

    /**
     * Mask string at the end of  $str
     * @param string $str string to be masked
     * @param string $suffix mask
     * @return string masked string
     */
    private static function rmask(String $str, $suffix = "")
    {
        if ($suffix !== "") {
            if (substr($str, strlen($str) - strlen($suffix)) == $suffix) {
                $str = substr($str, strlen($suffix));
            }
        }

        return rtrim($str, "/");
    }

    /**
     * Match given array of url parameters with correspondant pattern toknes as in array order
     * Only parameters inside {} are matched , others are considered as constants and ignored
     *
     * @param array $url
     * @param array $pattern
     * @return array $map
     */
    private static function matchPattern(array $url, array $pattern)
    {
        $map = [];

        /* if Url is longer than specified pattern
        all rest unmatches will be concatenated by '/' as {controller}
         */
        if (count($url) > count($pattern)) {
            for ($i = 0; $i < count($url); $i++) {
                $param = $pattern[$i];
                if (preg_match("/\A{(\w+)}$/", $param, $matches) === 1) {
                    $key = $matches[1];
                    if ($key == "controller") {

                        $remainParams = count($pattern) - $i - 1;
                        $path = array_splice($url, $i, -$remainParams, ["empty"]);

                        $map[$key] = implode("/", $path);

                    } else {
                        $map[$key] = $url[$i];
                    }

                }

            }
        } else if (count($url) <= count($pattern)) {
            /* match maximum parameters */

            for ($i = 0; $i < count($pattern); $i++) {
                $param = $pattern[$i];
                if ($i >= count($url)) {
                    break;
                }

                if (preg_match("/\A{(\w+)}$/", $param, $matches) === 1) {
                    $key = $matches[1];
                    $map[$key] = $url[$i];
                }
            }
        }

        return $map;
    }
}
