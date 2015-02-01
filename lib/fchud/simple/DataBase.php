<?php

namespace fchud\simple;

use fchud\simple\Debug as debug;
use Exception;
use PDO;

/**
 * simplified db class
 */
class DataBase {

    /**
     *
     * @var PDO the PHP PDO instance associated with this DB connection. 
     */
    private $pdo;

    /**
     * 
     * @param array $dbSet expects ['dsn'], ['username'] and ['password'] elements as strings
     */
    public function __construct($dbSet) {
        try {
            $this->pdo = new PDO($dbSet['dsn'], $dbSet['username'], $dbSet['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $ex) {
            debug::showEx($ex);
        }
    }

    public function __destruct() {
        $this->pdo = null;
    }

    /**
     * begins transaction
     * 
     * use cancel() to rollback
     */
    public function begin() {
        $this->pdo->beginTransaction();
    }

    /**
     * self explanotary
     */
    public function commit() {
        $this->pdo->commit();
    }

    /**
     * rolls back transaction started with begin()
     */
    public function cancel() {
        $this->pdo->rollBack();
    }

    /**
     * returns PDO instance
     * @return PDO
     */
    public function getDb() {
        return $this->pdo;
    }

    /**
     * executes SQL statement
     * 
     * prepares $statement and executes with $args
     * @param string $statement must be a valid SQL statement for the target database server
     * @param array $args holds one or more key=>value pairs to set attribute 
     *  values for the PDOStatement object that this method returns
     * @return PDOStatement returns an instance of PDOStatement or NULL on fail
     */
    public function execSql($statement, $args = []) {
        $pdoStatement = $this->pdo->prepare($statement);

        return $pdoStatement->execute($args) ? $pdoStatement : null;
    }

    /**
     * inserts $data[column => value, ...] into $table and returns last inserted ID
     * 
     * if the lastInsertId() returns empty value, its assumed that $data is already in the table.
     * so the $uniq parameter identifies the column name to get the ID using this $data.
     * 
     * @param string $table target table
     * @param array $data array of [COLUMN => VALUE, ...] elements to be
     *  inserted in a $table
     * @param string $uniq [optional] column name (usually primary key).
     *  used to get uniq id, using provided $data
     * @return integer the ID of the last inserted row
     */
    public function insert($table, $data, $uniq = false) {
        if (!is_string($table) || !is_array($data)) {
            return null;
        }

        $cols = '';
        $values = '';
        $where = '';
        $args = [];
        foreach ($data as $col => $val) {
            $cols .= $cols ? ", {$col}" : $col;
            $values .= $values ? ', ' : '';
            $values .= ":{$col}";

            $where .= $where ? ' AND ' : '';
            $where .= "{$col} = :{$col}";

            $args[":{$col}"] = $val;
        }
        $insert = "INSERT IGNORE INTO {$table} ({$cols}) VALUES ({$values})";

        $this->execSql($insert, $args);

        $lastID = $this->pdo->lastInsertId();
        if (!$lastID && $uniq) {
            $select = "SELECT {$uniq} FROM {$table} where {$where}";

            $id = $this->getSome($select, $args);
            if (isset($id[0][$uniq])) {
                $lastID = $id[0][$uniq];
            }
        }

        return $lastID ? $lastID : null;
    }

    /**
     * selects $what (columns) from $from (tables) with specified $when (conditions)
     * 
     * @param array $from tables
     * @param array $what columns
     * @param array $when conditions
     * @param array $options other specifiers like 'limit, order, ...'
     * @return array returns an array containing all of the remaining rows in the result set.
     *  the array represents each row as either an array of column values or an object
     *  with properties corresponding to each column name. An empty array is returned
     *  if there are zero results to fetch or on failure.
     */
    public function select($from, $what = [], $when = [], $options = []) {
        $cols = $what ? implode(', ', $what) : '*';
        $tables = implode(', ', $from);
        $where = $when ? ' WHERE ' . implode(' AND ', $when) : '';
        $other = $options ? ' ' . implode(' ', $options) : '';
        $select = "SELECT {$cols} FROM {$tables}{$where}{$other}";

        $rows = $this->getSome($select);

        return $rows;
    }

    /**
     * executes SQL SELECT statement and returns array of rows selected
     * 
     * @param string $statement must be a valid SQL statement for the target database server
     * @param array $args holds one or more key=>value pairs to set attribute 
     * @return array returns an array containing all of the remaining rows in the result set.
     *  the array represents each row as either an array of column values or an object
     *  with properties corresponding to each column name. An empty array is returned
     *  if there are zero results to fetch or on failure.
     */
    public function getSome($statement, $args = []) {
        $pdoStatement = $this->execSql($statement, $args);

        return $pdoStatement ? $pdoStatement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

}
