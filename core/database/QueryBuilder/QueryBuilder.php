<?php
/**
 * Created by PhpStorm.
 * User: wassim
 * Date: 05/04/18
 * Time: 17:22
 */

/**
 * Usage example
 * $qb = new QueryBuilder($con);
 * $qb->select(array('id','first_name'))
 * ->from ("employees")
 * ->where('salary','commission_pct')->equals(1)
 * ->or()->lessThen('2')
 * ->and('department_id')->isNot('null')
 * ->or()
 * ->('last_name')->like("w%");
 * $qb->execute();
 */

namespace AlphaDB;

require_once "BuilderInterface.php";

// TODO : add 'on' condition support alongside with 'where' and 'having'

class QueryBuilder implements BuilderInterface
{
    private $connection;

    private $generatedQuery;

    private $tables; // array of current working tables

    private $projectionColumns; // array of columns being projected.
    private $restrictionColumns; //array of tmp restriction columns used in where() | having()

    /**
     * @var array conditions currently being generated by multiple where() | having()
     */
    private $conditions;
    private $notFlag;

    private $generatedWhereCondition; // string of final where condition generated.
    private $generatedHavingCondition; // string og final having condition generated.

    private $lastCalledFunc;

    private $query; // final query

    public function __construct()
    {
        $this->projectionColumns = array();
        $this->restrictionColumns = array();
        $this->tables = array();
    }

    /**
     * Gets the query as a string.
     *
     * @return string
     */
    public function getQuery()
    {
        $this->buildQuery();
        return $this->query;

    }

    private function buildQuery()
    {
        if ($this->query == "SELECT") {
            $this->query .= " " . implode(",", $this->projectionColumns);
            $this->query .= " FROM " . implode(",", $this->tables);
            $this->query .= " WHERE " . $this->generatedWhereCondition;
        }
    }

    /**
     * Selects the table to use for this query. This method does not validate whether
     * the table exists in the database, thus any validation should be implemented
     * independently.
     *
     * @param string $name A string representing the table name, not including a common prefix.
     * @return BuilderInterface Returns the updated query builder object.
     * @throws DBCException
     */
    public function selectDb($name)
    {
        $callable = array($this->connection, $this->connection->dbName . "SelectDb");
        if (!is_callable($callable)) {
            throw new DBCException("unsupported database $this->connection->dbName");
        }
        $callable($name);
        return $this;
    }

    /**
     * Marks one or more columns from the table for selection. This method must accept
     * a single array of columns, or multiple parameters each representing a column.
     *
     * It also must append the columns to the selection if they do not already exist,
     * rather than overwriting previously selected columns.
     *
     * @param array|string $options
     * @return QueryBuilder Returns the updated query builder object.
     */
    public function select($options)
    {
        if (!is_array($options)) {
            $options = explode(",", $options);
        }
        $this->projectionColumns = array_merge($this->projectionColumns, $options);
        $this->lastCalledFunc = "select";
        $this->query = "SELECT";
        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function from()
    {
        $tables = func_get_args();
        $this->tables = array_merge($this->tables, $tables);
        $this->lastCalledFunc = "from";
        return $this;
    }

    /**
     * Starts a 'where' match statement. An unlimited number of parameters are accepted,
     * each representing a column to match. A subsequent comparator method must be called
     * immediately on the object.
     *
     * If multiple columns are provided, they will be compared individually to the same
     * criteria (equivalent to an AND statement).
     *
     * For example:
     *
     *     $query->where('username')->equals(5)
     *     $query->where('points', 'score')->greaterThan(0)
     *
     * @return QueryBuilder Returns the updated query builder object.
     */
    public function where()
    {
        if (func_num_args() > 0) {
            $this->restrictionColumns = func_get_args();
        }
        $this->lastCalledFunc = "where";

        return $this;
    }

    /**
     * Can be called to restrict new columns or add current columns restriction rules .
     * Example :
     *  ->where('username')->equals('john')->or_()->equals('doe');
     *      or
     *  ->where('first_name')->equals('john')->or_('last_name')->equals('doe');
     *
     * @return QueryBuilder
     * @throws QBException
     */
    public function or_()
    {
        /* update restriction columns */
        if (func_num_args() > 0) {
            $this->restrictionColumns = func_get_args();
        }
        return $this->boolRestrict(" OR ");
    }


    /**
     * Can be called to restrict new columns or add current columns restriction rules .
     * Example :
     *  ->where('username')->equals('john')->and_()->equals('doe');
     *      or
     *  ->where('first_name')->equals('john')->and_('last_name')->equals('doe');
     *
     * @return QueryBuilder
     * @throws QBException
     */
    public function and_()
    {
        /* update restriction columns */
        if (func_num_args() > 0) {
            $this->restrictionColumns = func_get_args();
        }
        return $this->boolRestrict("AND");

    }

    /**
     * called by or_() | and_() , updates columns restriction as mentioned in those two functions.
     * @param $bool
     * @return $this
     * @throws QBException
     */
    private function boolRestrict($bool)
    {


        /* append 'OR' to condition */
        if (!empty($this->conditions)) {
            if ($this->lastCalledFunc == "where") {
                $this->generatedWhereCondition .= " $bool ";
            } else if ($this->lastCalledFunc == "having") {
                $this->generatedHavingCondition .= " $bool ";
            }
        } else {
            throw new QBException("can't $bool() at beginning of condition");
        }

        $this->conditions = null;
        return $this;
    }

    /**
     * @throws QBException
     */
    private function generateCondition()
    {

        if ($this->lastCalledFunc == "where") {
            $this->generatedWhereCondition .= implode(" AND ", $this->conditions);
        } else if ($this->lastCalledFunc == "having") {
            $this->generatedHavingCondition .= implode(" AND ", $this->conditions);
        } else {
            throw new QBException("equals can't be called after $this->lastCalledFunc");
        }
    }

    /**
     * Updates one or more columns in matching rows to the provided values. The expected
     * input is an array, where columns are represented as keys and their associated values
     * are set.
     *
     * @param array $update
     * @return void True on success, false on failure.
     */
    public function update(array $update)
    {
        // TODO: Implement update() method.
    }

    /**
     * Inserts one or more rows into the table. The provided array must contain
     * child arrays with the columns and values to insert.
     *
     * @param array $rows
     *
     * @throws QBException When a query error occurs.
     * @return bool True on success, false on failure.
     */
    public function insert(array $rows)
    {
        // TODO: Implement insert() method.
    }

    /**
     * Inserts a row into a table with an auto incrementing primary key. The inserted
     * ID will be returned.
     *
     * @throws QueryException When a query error occurs.
     * @return int The inserted ID.
     */
    public function insertGetId(array $row)
    {
        // TODO: Implement insertGetId() method.
    }

    /**
     * Deletes the selected rows. If no criteria has been set for this query, all rows
     * will be deleted, but the table will not be truncated.
     *
     * @throws QueryException When a query error occurs.
     * @return bool True on success, false on failure.
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * Executes the query and returns all results as associative arrays.
     *
     * @throws QueryException When a query error occurs.
     * @return array An associative array of all results.
     */
    public function get()
    {
        // TODO: Implement get() method.
    }

    /**
     * Returns the first matching row as an associative array, or null if there are
     * no rows. This method will automatically apply a LIMIT of 1 row to the query.
     *
     * @return array|null Returns the matched row or null.
     */
    public function first()
    {
        // TODO: Implement first() method.
    }

    /**
     * Returns the first matching row as an associative array, or causes a 404 error
     * by throwing an HttpException if there are no matches.
     *
     * @throws HttpException
     * @return array|null Returns the matched row or null.
     */
    public function firstOrFail()
    {
        // TODO: Implement firstOrFail() method.
    }

    /**
     * Returns the aggregate number of rows which match the current criteria.
     *
     * @return integer Returns the number of matching rows.
     */
    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * Sorts the results by the specified column, and optionally the specified
     * direction (asc, desc). Calls to this method will stack and appear in the
     * query in the same order.
     *
     * @param string $by
     * @param string|null $direction
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function orderBy($by, $direction = null)
    {
        // TODO: Implement orderBy() method.
    }

    /**
     * Groups the results by the specified column.
     *
     * @param string $by
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function groupBy($by)
    {
        // TODO: Implement groupBy() method.
    }

    /**
     * Limits the number of rows to the specified number. If the provided limit
     * is less than or equal to 0, then the limit will be removed.
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function limit($n)
    {
        // TODO: Implement limit() method.
    }

    /**
     * Offsets the results by the specified number of rows. The default offset
     * for all queries is 0, and any values less than 0 will be reset to 0.
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function offset($n)
    {
        // TODO: Implement offset() method.
    }

    /**
     * Truncates the current table, effectively deleting all rows and resetting
     * auto increments. This method ignores foreign key restraints.
     *
     * @return boolean
     */
    public function truncate()
    {
        // TODO: Implement truncate() method.
    }

/*********************************** simple comparesion operators *************************/

    /**
     * This method is used immediately after calling where()|having() and will match
     * the specified column(s) to the provided value. Under certain server-side
     * circumstances, this will be case-insensitive.
     *
     * @param string|int|double $n
     *
     * @return QueryBuilder Returns the updated query builder object.
     * @throws QBException
     */
    public function equals($n)
    {
        if($this->notFlag){
            $op = "!=";
            $this->notFlag = false;
        }else{
            $op = "⁼";
        }

        $this->compare($op,$n);
        $this->generateCondition();
        return $this;
    }

    public function not()
    {
        $this->notFlag = true;
        return $this;
    }

    public function lessThan($n)
    {
        if($this->notFlag){
            $op = ">=";
            $this->notFlag = false;
        }else{
            $op = "<";
        }

        $this->compare($op,$n);
        $this->generateCondition();
        return $this;
    }

    public function greaterThan($n)
    {
        if($this->notFlag){
            $op = "<=";
            $this->notFlag = false;
        }else{
            $op = ">";
        }

        $this->compare($op,$n);
        $this->generateCondition();
        return $this;
    }
    /**
     * This method is used immediately after calling where(), and will compare
     * the specified column(s) to the provided value. Under certain server-side
     * circumstances, this will be case-insensitive.
     *
     * @param string|int|double $n
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function lessThanEqual($n)
    {
        if($this->notFlag){
            $op = ">";
            $this->notFlag = false;
        }else{
            $op = "<=";
        }

        $this->compare($op,$n);
        $this->generateCondition();
        return $this;
    }

    /**
     * This method is used immediately after calling where(), and will compare
     * the specified column(s) to the provided value. Under certain server-side
     * circumstances, this will be case-insensitive.
     *
     * @param string|int|double $n
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function greaterThanEqual($n)
    {
        if($this->notFlag){
            $op = "<";
            $this->notFlag = false;
        }else{
            $op = ">=";
        }

        $this->compare($op,$n);
        $this->generateCondition();
        return $this;
    }


    /**
     * @param $operator
     * @param $str
     * @param null $escape
     */
    private function  compare($operator, $str, $escape = null){
        foreach ($this->restrictionColumns as $column) {
            $this->conditions[] = $column . " $operator $str ";
        }
    }
/*********************************************************************************************/





    /**
     * This method is used immediately after calling where(), and will check if
     * the values of the specified column(s) fall between a and b.
     *
     * @param int|double $a
     * @param int|double $b
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function between($a, $b)
    {
        // TODO: Implement between() method.
    }


    /**
     * This method is used immediately after calling where(), and will check if
     * the values of the specified column(s) are one of the specified values.
     *
     * This method accepts values as individual parameters, or within a single
     * array as the first argument.
     *
     * @param array|string|int|double $values
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function in($values)
    {
        // TODO: Implement in() method.
    }


    /**
     * This method is used immediately after calling where(), and will check if
     * the values of the specified column(s) is of the specified type. For
     * example, is('null') or is('string').
     *
     * Multiple types can be provided as individual parameters, or by providing
     * an array as the first parameter, and will be joined together using OR.
     *
     * @param array|string $str
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function is($str)
    {
        // TODO: Implement is() method.
    }

    /**
     * This method is used immediately after calling where(), and will check if
     * the values of the specified column(s) is not of the specified type(s). For
     * example, isNot('null') or isNot('string').
     *
     * Multiple types can be provided as individual parameters, or by providing
     * an array as the first parameter, and will be joined together using AND.
     *
     * @param array|string $str
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function isNot($str)
    {
        // TODO: Implement isNot() method.
    }

    /**
     * This method is used immediately after calling where(), and will compare
     * the specified column(s) to the provided value using LIKE. Under certain
     * server-side circumstances, this will be case-insensitive.
     *
     * Although the provided value will be escaped, any wildcards (%) prefixed
     * or suffixed to the string will be applied to the operator as expected.
     *
     * @param string $str
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function like($str)
    {
        // TODO: Implement like() method.
    }

    /**
     * This method is used immediately after calling where(), and will compare
     * the specified column(s) to the provided value using LIKE. Under certain
     * server-side circumstances, this will be case-insensitive.
     *
     * Although the provided value will be escaped, any wildcards (%) prefixed
     * or suffixed to the string will be applied to the operator as expected.
     *
     * The provided value will be matched as BINARY; that is, an exact match
     * of the two data.
     *
     * @param string $str
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function likeBinary($str)
    {
        // TODO: Implement likeBinary() method.
    }

    /**
     * This method is used immediately after calling where(), and will compare
     * the specified column(s) to the provided pattern using REGEXP.
     *
     * This is the only method which does not have automatic escaping. If including
     * user input, be cautious and escape it as needed.
     *
     * @param string $pattern
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    public function regexp($pattern)
    {
        // TODO: Implement regexp() method.
    }



}