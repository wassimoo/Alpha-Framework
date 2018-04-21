<?php
/**
 * Created by PhpStorm.
 * User: wassim
 * Date: 27/02/18
 * Time: 17:31
 */
namespace AlphaRouter;

require_once __DIR__ . "/../finder/Finder.php";
require_once __DIR__ . "/../router/Dispatcher.php";

use AlphaFinder\Finder;
use AlphaFinder\InvalidPathException;
use AlphaFinder\PathNotFoundException;

define("DEFAULT_PATTERN", "{controller}/{method}/{id}");

class Router
{
    private static $finder = null; //finder object

    private $actions = array("regex" => array(), "direct" => array());
    public $projectRoot;
    public $defaultController;
    private $autoResponseMatch;
    /**
     * @var String prefix for url , will be ignored when routing .
     */
    private $uriPrefix;

    /**
     * @var string $pattern, controller calling rules
     * example : {controller}/{method}/{id}
     * {controller} => name of controller
     * {method} => name of methode to be called
     * {id} => methode param id will be matched
     * rest => rest of url dispatched parameters that could not be matched with pattern
     */
    public $pattern;

    /**
     * Router constructor.
     * @param String $projectRoot main tests root folder where MVC exists
     * @param String $defaultController default controller path , this file is called when no specific page requested
     * @param bool $autoResponseMatch
     */
    public function __construct(String $projectRoot, String $defaultController = null, bool $autoResponseMatch = true, string $pattern = "") /*String $viewsDir = null, String $cssDir = null, String $scriptsDir = null, String $fontsDir = null*/
    {
        $this->projectRoot = $projectRoot;

        if ($defaultController === "") //assert $default controller is specified or null
        {
            $defaultController = null;
        }

        if ($defaultController == null || $autoResponseMatch) {
            /* || $viewsDir == null || $cssDir == null || $scriptsDir == null || $fontsDir == null */

            require_once __DIR__ . "/../finder/Finder.php";
            try {
                if (!isset(self::$finder)) {
                    self::$finder = new Finder($projectRoot, array("vendor", "models"), 5);
                }
                //TODO : exclude self(whether in vendor or in models) and cached folders
            } catch (PathNotFoundException $e) {
                echo $e;
                return;
            } catch (InvalidPathException $e) {
                echo $e;
                return;
            }

            //$this->viewsDir = $viewsDir ?? self::$finder->findDir("(view|template|html|web|page)s?");
            //$this->cssDir = $cssDir ?? self::$finder->findDir("(assets?\/)?(css)|(style|stylesheet)s?");
            //$this->scriptsDir = $scriptsDir ?? self::$finder->findDir("(assets?\/)?js|(javascript|script)s?");
            //$this->fontsDir = $fontsDir ?? self::$finder->findDir("(assets?\/)?fonts?");
            $this->defaultController = self::$finder->findFile("defaultController.php");
        } else {
            $this->defaultController = $defaultController;
        }

        $this->actions = array();
        $this->autoResponseMatch = $autoResponseMatch;
        $this->setNotFound("ErrorPages/404.html");

        if ($pattern == "") {
            $this->pattern = DEFAULT_PATTERN;
        } else {
            $this->pattern = $pattern;
        }
    }

    public function route()
    {
        $urlMap = Dispatcher::dispatch($this->uriPrefix, $this->pattern);

        if (empty($urlMap["controller"])) {
            $this->redirect($this->defaultController);
            return;
        }

        if (empty($urlMap["method"])) {
            $url["method"] = "control";
        }

        if ($this->autoResponseMatch) {
            if ($this->autoRespond($urlMap)) {
                return;
            }
        }

        if (array_key_exists($urlMap["controller"], $this->actions["direct"])) {

            /**
             * we match request handler here,
             * we can safely use isset even it may return false if value is NULL,
             * however map() function allows only string with  non empty values.
             */

            $this->redirect($this->projectRoot . "/controllers/" . $this->actions["direct"][$urlMap["controller"]]);
        } else {

            /**
             * Search for matching pattern;
             */

            foreach ($this->actions["regex"] as $reg => $action) {
                if (preg_match($reg, $urlMap["controller"])) {
                    $this->redirect($this->projectRoot . "/controllers/" . $action);
                    return;
                }
            }

            /**
             * No match found ! redirect to default Controller
             */

            $this->redirect($this->defaultController);
        }
        // Controller will be determined based on url path;

        return;
    }

    /**
     * For instance it's not recommended to put one or more controller files having the same case-sensitive names,
     * This will cause in an undefined behavior , it's in our TODO list.
     *
     * @param array $urlMap
     */

    private function autoRespond(array $urlMap)
    {
        if (self::$finder == null) {
            $this->redirect($this->defaultController);
        } else {

            $targetControllerPath = $this->projectRoot . "/controllers/" . rtrim($urlMap["controller"], ".php") . ".php";
            $targetControllerPath = self::$finder->findFile($targetControllerPath, false);

            if ($targetControllerPath == false) //not found
            {
                header("Location:" . $this->actions["direct"]["404"]);
                exit();
            } else {
                $args = array_diff($urlMap, [$urlMap["method"], $urlMap["controller"]]);
                $this->redirect($targetControllerPath, $urlMap["method"], $args);
            }
        }
    }

    /**
     * Call requested method with args that can be matched by $params
     * @var string $classFile path to file
     * @var string $method method name to be called
     * @var array $params args
     */
    private function redirect(String $classFile, string $method = "control", array $params = [])
    {
        try {
            require_once $classFile;
            $class = basename($classFile, ".php");
            $reflection = new \ReflectionMethod(basename($class, ".php"), $method);
            $namespace = $reflection->getNamespaceName();
            $num_params = $reflection->getNumberOfRequiredParameters();
            if ($num_params > count($params)) { //TODO : add required parameters check !
                return false;
            } else {
                $fire_args = array();

                foreach ($reflection->getParameters() as $arg) {
                    if (array_key_exists($arg->name, $params)) {
                        $fire_args[$arg->name] = $params[$arg->name];
                    } else {
                        $fire_args[$arg->name] = null;
                    }
                }
                call_user_func_array(array($class, $method), $fire_args);
            }
        } catch (ReflectionException $ex) {
            $msg = "Invalid controller ($classFile::$method) can't call method or file does not exist\n";
            error_log("WARNING (Router.php) " . date("Y-m-d H:i:s") . " : $msg ", 3, __DIR__ . "/../logs/router.log");
            if (isset($this->actions["direct"]["404"]) && file_exists($this->actions["direct"]["404"])) {
                header("Location:" . $this->actions["direct"]["404"]);
                exit();
            } else {
                $msg = "Can't find 404 page file\n";
                error_log("WARNING (Router.php) " . date("Y-m-d H:i:s") . " : $msg", 3, __DIR__ . "/../logs/router.log");
            }
        }
    }

    /**
     *  maps request to it's controller ,
     * @param String $request | regex
     * @param String $response
     * @param bool $isRegex
     * @return bool set|update success
     */
    public function map(String $request, String $response, bool $isRegex = false)
    {
        if (is_string($request) && is_string($response) && $request != "" && $response != "") {
            if ($isRegex) {
                $this->actions["regex"]["/" . $request . "/"] = $response;
            } else {
                $this->actions["direct"][$request] = $response;
            }
            return true;
        }
        return false;
    }

    /**
     * @param String $response
     */
    public function setNotFound(String $response)
    {
        $this->actions["direct"]["404"] = $response;

    }

    /**
     * To force boolean value
     * @param bool $response
     */
    public function setAutoResponseMatch(bool $response)
    {
        if ($response != $this->autoResponseMatch) {
            $this->autoResponseMatch = !$this->autoResponseMatch;
        }

    }

    /**
     * Set uri_prefix , to clear use clearPrefix()
     * @param String $prefix lowercase and non empty string
     */
    public function setPrefix(string $prefix)
    {
        if (!empty($prefix)) {
            $this->uriPrefix = $prefix;
        }
    }

    public function clearPrefix()
    {
        $this->uriPrefix = "";
    }
}
