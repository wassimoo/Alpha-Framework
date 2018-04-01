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


class Router
{
    private static $finder = null; //finder object
    //public $viewsDir;
    //public $cssDir;
    //public $scriptsDir;
    //public $fontsDir;
    public $actions = array("regex" => array(), "direct" => array());
    public $projectRoot;
    public $defaultController;
    private $autoResponseMatch;

    /* TODO : should the project root be passed or controllers folder only ? */

    /**
     * Router constructor.
     * @param String $projectRoot main tests root folder where MVC exists
     * @param String $defaultController default controller path , this file is called when no specific page requested
     * @param bool $autoResponseMatch
     */
    public function __construct(String $projectRoot, String $defaultController = null, bool $autoResponseMatch = true)/*String $viewsDir = null, String $cssDir = null, String $scriptsDir = null, String $fontsDir = null*/
    {
        $this->projectRoot = $projectRoot;

        if ($defaultController === "") //assert $default controller is specified or null
            $defaultController = null;

        if ($defaultController == null || $autoResponseMatch) {
            /* || $viewsDir == null || $cssDir == null || $scriptsDir == null || $fontsDir == null */

            require_once __DIR__ . "/../finder/Finder.php";
            try {
                if (!isset(self::$finder))
                    self::$finder = new Finder($projectRoot, array("vendor", "models"), 5); //TODO : exclude self(whether in vendor or in models) and cached folders
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
    }

    public function route()
    {
        $url = Dispatcher::dispatch();
        if (empty($url)) {
            $this->redirect($this->defaultController);
            return;
        }

        if ($this->autoResponseMatch) {
            if ($this->autoRespond($url))
                return;
        }

        if (array_key_exists($url, $this->actions["direct"])) {

            /**
             * we match request handler here,
             * we can safely use isset even it may return false if value is NULL,
             * however map() function allows only string with  non empty values.
             */

            $this->redirect($this->projectRoot . "/controllers/" . $this->actions["direct"][$url]);
        } else {

            /**
             * Search for matching pattern;
             */

            foreach ($this->actions["regex"] as $reg => $action) {
                if (preg_match($reg, $url)) {
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
     * @param String $url
     * @return bool controller match success/failure
     */

    private function autoRespond(String $url)
    {
        if (self::$finder == null) {
            return false;
        } else {
            $targetControllerPath = $this->projectRoot . "/controllers/" . rtrim($url, ".php") . ".php";
            $targetControllerPath = self::$finder->findFile($targetControllerPath, false);

            if ($targetControllerPath == false) //not found
                return false;

            else if (($callback = $this->isValidController($targetControllerPath)) != false) {
                $this->redirect($targetControllerPath, $callback);
                return true;
            }
            return false;
        }
    }

    private function redirect(String $classFile, String $callback = "")
    {
        if ($callback === "") {
            $callback = $this->isValidController($classFile);
        }

        //call Controller:: if found or redirect to 404
        $callback ? $callback() : header("Location:" . $this->actions["direct"]["404.html"]);
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
     * if file does not exist or control() method  not found
     *      returns false
     * else
     *      returns static method control() callable string
     *
     * @param String $classFile
     * @return bool|string
     */
    private function isValidController(String $classFile)
    {
        if (empty($classFile)) {
            return false;
        } else if (file_exists($classFile)) {
            require_once $classFile;
            $functionName = basename($classFile, ".php") . "::control";
            return is_callable($functionName) ? $functionName : false;
        } else {
            error_log("WARNING (Router.php) " . date("Y-m-d H:i:s") . " : Invalid controller ($classFile) file does not exist\n", 3, __DIR__ . "/../logs/router.log");
            return false;
        }
    }
}