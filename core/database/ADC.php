<?php

// Alpha Database Connector

/**
 * Supported Databases
 * oracle
 * MySQL
 * MS SQL Server
 * PostgresSQL
 * MongoDB
 * MariaDB
 */

namespace AlphaDB;
require_once "Exceptions/DBCException.php";

class ADC
{

    public $dbName;
    private $host;
    private $port;
    private $charset;
    private $defaultSchema;

    private $username;
    private $password;
    private $sysdba;

    private $connection;

    public function __construct($dbName, $host, $port, $charset, $schema)
    {
        $this->dbName = $dbName;
        $this->host = $host;
        $this->port = $port;
        $this->charset = $charset;
        $this->defaultSchema = $schema;
    }


    /* ************************** Connection *********************************** */
    /**
     * If host is not specified localhost is set by default.
     * @param $username
     * @param $password
     * @param bool $sysdba
     * @throws DBCException
     */
    public function _oracleConnect($username, $password, $sysdba)
    {
        if (empty($this->port))
            $this->port = 1521;
        if (empty($this->charset))
            $this->charset = "AL32UTF8";


        if ($this->host === "") {
            // username@[//]host[:port][/service_name][:server][/instance_name]
            $connStr = "localhost/XE:" . $this->port;
        } else {
            $connStr = "( DESCRIPTION= ( ADDRESS_LIST = ( ADDRESS = (PROTOCOL = TCP)(HOST = $this->host )(PORT = $this->port) ) ) ) )";
        }

        if (empty($username))
            throw new DBCException("No username has been specified");

        if ($sysdba)
            $conn = oci_connect($username, $password, $connStr, $this->charset, OCI_SYSDBA);
        else
            $conn = oci_connect($username, $password, $connStr, $this->charset);

        if (!$conn)
            throw new DBCException("can't connect to database error message : " . oci_error()["message"]);


        // Switching schema .
        if ($this->defaultSchema != "") {
            $query = "SELECT SYS_CONTEXT('userenv', 'current_schema' ) FROM dual";
            $stid = oci_parse($conn, $query);
            if (oci_execute($stid, OCI_NO_AUTO_COMMIT) == false) {
                throw new DBCException("Couldn't get current schema , error message " . oci_error()["message"]);
            } else if (oci_fetch_row($stid)[0] != strtoupper($this->defaultSchema)) {
                $query = "ALTER SESSION SET CURRENT_SCHEMA = " . $this->defaultSchema;
                $stid = oci_parse($conn, $query);
                if (oci_execute($stid, OCI_NO_AUTO_COMMIT) == false) {
                    $tmp = oci_error();
                    var_dump($tmp);
                    throw new DBCException("Couldn't switch to schema { $this->defaultSchema }");
                }
            }
        }
        $this->username = $username;
        $this->password = $password;
        $this->sysdba = $sysdba;

        $this->connection = $conn;
    }

    /**
     * @param $username
     * @param $password
     * @throws DBCException
     */
    public function _mysqlConnect($username, $password)
    {
        if (empty($this->port))
            $this->port = 3306;
        if (empty($this->charset))
            $this->charset = "UTF8";

        if (empty($username))
            throw new DBCException("No username has been specified");

        $conn = new \mysqli($this->host, $username, $password, $this->defaultSchema);
        if ($conn->connect_errno == 0) {
            $this->username = $username;
            $this->password = $password;
            $this->connection = $conn;
        } else if ($conn->connect_errno == 1045)
            throw new DBCException("can't connect to database error message Access denied for user");
        else if ($conn->connect_errno == 1049)
            throw new DBCException("Couldn't switch to schema { $this->defaultSchema }");
        else
            throw new Exception($conn->connect_error);

    }

    public function _pstgrsConnect($username, $password)
    {

    }

    /* ************************************************************************* */




    /* *************************** DB/Schema selection ************************* */

    /**
     * @param $name
     * @return bool|\mysqli_result
     * @throws DBCException
     */
    public function _mysqlSelectDb($name)
    {
        if (!$this->connection) {
            return mysqli_query($this->connection, "user $name");
        } else
            throw new DBCException("Invalid connection setup, can't query database selection ");
    }

    /**
     * @param $name
     * @return bool
     * @throws DBCException
     */
    public function _oracleSelectDb($name)
    {
        if (!$this->connection) {
            $query = "ALTER SESSION SET CURRENT_SCHEMA = $name";
            $stid = oci_parse($this->connection, $query);
            return oci_execute($stid);
        } else
            throw new DBCException("Invalid connection setup, can't query database selection ");

    }

    public function _pstgrsSelectDb($name)
    {

    }
    /* ************************************************************************* */

}