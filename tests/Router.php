<?php
require_once __DIR__ . "/../core/session/session.php";
require_once __DIR__ . "/../core/router/Router.php";

/*Session::startSession();
if (!isset($_SESSION["info"])) {
    $_SESSION["info"] = new Session(100 , 3 , array("username" => "wassimoo"));
}

if(!$_SESSION["info"]->validate()){
    echo "login again";
    return;
}
//$_SESSION["info"]->endSession();
var_dump($_SESSION["info"]);
echo session_id();
*/

/*
 * try{
    $finder = new AlphaFinder\Finder(__DIR__,2);
}catch ( AlphaFinder\PathNotFoundException $ex){
    echo $ex->getMessage();
}
catch (AlphaFinder\UnreadablePathException $ex){
    echo $ex->getMessage();
}catch (AlphaFinder\InvalidPathException $ex){
    echo $ex->getMessage(); 
}
var_dump($finder->getPaths());
*/
$router = new Router(__DIR__, "", true);
$router->map(".*", "MVC/app/example.php", true);
$router->map("mvc", "login.php");
$router->map("search", "search.php");
$router->map("mvc/tests/routers", "defaultController.php");
var_dump($router->actions);

$router->route();
//Dispatcher::dispatch();