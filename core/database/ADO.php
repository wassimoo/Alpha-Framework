<?php
/**
 * Created by PhpStorm.
 * User: wassim
 * Date: 29/03/18
 * Time: 16:50
 */

use AlphaDB\ADC;
use AlphaDB\DBCException;

require_once "ADC.php";

class ADO
{
    public $connector;

    public function __construct($dbName, $host, $port, $charset, $schema)
    {
        $this->connector = new ADC($dbName,$host,$port,$charset,$schema);
    }

    /**
     * @param $username
     * @param $password
     * @param $sysdba
     * @return mixed
     * @throws DBCException
     */
    public  function connect($username, $password, $sysdba = false)
    {
            $callable =  array($this->connector,$this->connector->dbName . "Connect");
            if(!is_callable($callable))
                throw new DBCException("unsupported database $this->dbName");

            $callable($username, $password, $sysdba);
    }

    public  function disconnect()
    {

    }

}


class DB
{
    const ORACLE = "_oracle";
    const SQL_SERVER = "_sServer";
    const MYSQL = "_mysql";
    const PSTGRS_SQL = "_pstgrs";
    const MONGO = "_monogo";
    const MARIA = "_maria";
}