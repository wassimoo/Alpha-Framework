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

class ADO extends ADC
{

    public function __construct($dbName, $host, $port, $charset, $schema)
    {
        $this->dbName = $dbName;
        $this->host = $host;
        $this->port = $port;
        $this->charset = $charset;
        $this->defaultSchema = $schema;

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
            $callable =  array($this,$this->dbName . "Connect");
            if(!is_callable($callable))
                throw new DBCException("unsupported database $this->dbName");

            return $callable($username, $password, $sysdba);
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