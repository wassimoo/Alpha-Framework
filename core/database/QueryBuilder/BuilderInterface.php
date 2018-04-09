<?php
namespace AlphaDB;

/**
 * 
 * Source : Dusk Framework. (https://github.com/baileyherbert/dusk)
 * Big thanks to @baileyherbert for his awesome framework, and for this interface .
 *  
 */

interface BuilderInterface
{
    /**
     * Gets the query as a string.
     *
     * @return string
     */
    function getQuery();

    /**
     * Selects the table to use for this query. This method does not validate whether
     * the table exists in the database, thus any validation should be implemented
     * independently.
     *
     * @param string $name A string representing the table name, not including a common prefix.
     * @return BuilderInterface Returns the updated query builder object.
     */
    function selectDb($name);

    /**
     * Marks one or more columns from the table for selection. This method must accept
     * a single array of columns, or multiple parameters each representing a column.
     *
     * It also must append the columns to the selection if they do not already exist,
     * rather than overwriting previously selected columns.
     *
     * @param array|string $options
     * @return BuilderInterface Returns the updated query builder object.
     */
    function select($options);

    /**
     * Updates one or more columns in matching rows to the provided values. The expected
     * input is an array, where columns are represented as keys and their associated values
     * are set.
     *
     * @throws QueryException When a query error occurs.
     * @return bool True on success, false on failure.
     */
    function update(array $update);

    /**
     * Inserts one or more rows into the table. The provided array must contain
     * child arrays with the columns and values to insert.
     *
     * @param array $rows
     *
     * @throws QueryException When a query error occurs.
     * @return bool True on success, false on failure.
     */
    function insert(array $rows);

    /**
     * Inserts a row into a table with an auto incrementing primary key. The inserted
     * ID will be returned.
     *
     * @throws QueryException When a query error occurs.
     * @return int The inserted ID.
     */
    function insertGetId(array $row);

    /**
     * Deletes the selected rows. If no criteria has been set for this query, all rows
     * will be deleted, but the table will not be truncated.
     *
     * @throws QueryException When a query error occurs.
     * @return bool True on success, false on failure.
     */
    function delete();

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
     * @return BuilderInterface Returns the updated query builder object.
     */
    function where();

    /**
     * Starts a 'where' match statement. An unlimited number of parameters are accepted,
     * each representing a column to match. A subsequent comparator method must be called
     * immediately on the object.
     *
     * If there are other 'where' statements already in queue, this statement will be
     * prepended with 'OR'.
     *
     * If multiple columns are provided, they will be compared individually to the same
     * criteria (equivalent to an AND statement).
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    //function orWhere();

    /**
     * Executes the query and returns all results as associative arrays.
     *
     * @throws QueryException When a query error occurs.
     * @return array An associative array of all results.
     */
    function get();

    /**
     * Returns the first matching row as an associative array, or null if there are
     * no rows. This method will automatically apply a LIMIT of 1 row to the query.
     *
     * @return array|null Returns the matched row or null.
     */
    function first();

    /**
     * Returns the first matching row as an associative array, or causes a 404 error
     * by throwing an HttpException if there are no matches.
     *
     * @throws HttpException
     * @return array|null Returns the matched row or null.
     */
    function firstOrFail();

    /**
     * Returns the aggregate number of rows which match the current criteria.
     *
     * @return integer Returns the number of matching rows.
     */
    function count();

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
    function orderBy($by, $direction = null);

    /**
     * Groups the results by the specified column.
     *
     * @param string $by
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    function groupBy($by);

    /**
     * Limits the number of rows to the specified number. If the provided limit
     * is less than or equal to 0, then the limit will be removed.
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    function limit($n);

    /**
     * Offsets the results by the specified number of rows. The default offset
     * for all queries is 0, and any values less than 0 will be reset to 0.
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    function offset($n);

    /**
     * Truncates the current table, effectively deleting all rows and resetting
     * auto increments. This method ignores foreign key restraints.
     *
     * @return boolean
     */
    function truncate();

    /**
     * This method is used immediately after calling where(), and will match
     * the specified column(s) to the provided value. Under certain server-side
     * circumstances, this will be case-insensitive.
     *
     * @param string|int|double $n
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    function equals($n);

    /**
     * This method is used immediately after calling where(), and will compare
     * the specified column(s) to the provided value. Under certain server-side
     * circumstances, this will be case-insensitive.
     *
     * @param string|int|double $n
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    function lessThan($n);

    /**
     * This method is used immediately after calling where(), and will compare
     * the specified column(s) to the provided value. Under certain server-side
     * circumstances, this will be case-insensitive.
     *
     * @param string|int|double $n
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    function lessThanEqual($n);

    /**
     * This method is used immediately after calling where(), and will compare
     * the specified column(s) to the provided value. Under certain server-side
     * circumstances, this will be case-insensitive.
     *
     * @param string|int|double $n
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    function greaterThan($n);

    /**
     * This method is used immediately after calling where(), and will compare
     * the specified column(s) to the provided value. Under certain server-side
     * circumstances, this will be case-insensitive.
     *
     * @param string|int|double $n
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    function greaterThanEqual($n);

    /**
     * This method is used immediately after calling where(), and will compare
     * the specified column(s) to the provided value. Under certain server-side
     * circumstances, this will be case-insensitive.
     *
     * @param string|int|double $n
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    function not();


    /**
     * This method is used immediately after calling where(), and will check if
     * the values of the specified column(s) fall between a and b.
     *
     * @param int|double $a
     * @param int|double $b
     *
     * @return BuilderInterface Returns the updated query builder object.
     */
    function between($a, $b);


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
    function in($values);


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
    function is($str);

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
    function isNot($str);

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
    function like($str);

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
    function likeBinary($str);

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
    function regexp($pattern);
}