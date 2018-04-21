<?php

use AlphaDB\QueryBuilder;
use AlphaDB\QBException;
use AlphaRouter\Router;

require_once __DIR__ . "/../core/session/session.php";
require_once __DIR__ . "/../core/router/Router.php";
require_once __DIR__ . "/../core/database/ADO.php";
require_once  __DIR__ . "/../core/database/QueryBuilder/QueryBuilder.php";
require_once  __DIR__ . "/../core/database/Exceptions/QBException.php";

/*
Session::startSesAlphaRoutersion();
if (!isset($_SESSION["info"])) {
    $_SESSION["info"] = new Session(100 , 5 , array("username" => "wassimoo"));
}


if(!$_SESSION["info"]->validate()){
    echo "login again";
    return;
}
//$_SESSION["info"]->endSession();
var_dump($_SESSION["info"]);
echo session_id();

exit();
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


$router = new Router(__DIR__, "", false);
$router->setPrefix("Alpha-Framework/tests");

$router->map(".*", "MVC/app/Example.php", true);
$router->map("mvc", "login.php");
$router->map("search", "search.php");
$router->map("mvc/tests/routers", "defaultController.php");

$router->route();
exit();

//Connection::xa();

/*
$x = new ADO(DB::ORACLE,'','','','');
try{
    $x->connect("SYS","Wael5121997",true);
}catch (\AlphaDB\DBCException $e){
    echo $e->getMessage();
}*/


$ado = new ADO(DB::ORACLE,'','','','');
$qb = new QueryBuilder();
try{
    $qb->select('first_name, last_name ,commission_pct')->from('Employees')
        ->where("first_name","last_name")->equals("'john'")
        ->or_("salary")
        ->not()->between("4","5");
    echo $qb->getQuery();
}catch (QBException $e){
    echo $e->getMessage();
}

