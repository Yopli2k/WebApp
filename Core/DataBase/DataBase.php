<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace WebApp\Core\DataBase;

use WebApp\Core\App\Message;
use mysqli;

/**
 * Generic class of access to the MySQL database.
 *
 * @author Carlos García Gómez           <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
final class DataBase
{

    /**
     * Link to the database engine.
     *
     * @var MysqlEngine
     */
    private static $engine;

    /**
     * The link with de database.
     *
     * @var mysqli
     */
    private static $link;

    /**
     * DataBase constructor and prepare the class to use it.
     * Singleton pattern.
     */
    public function __construct()
    {
        if (self::$link === null) {
            self::$engine = new MysqlEngine();
        }
    }

    /**
     * Start a transaction in the database.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        if ($this->inTransaction()) {
            return true;
        }
        return self::$engine->beginTransaction(self::$link);
    }

    /**
     * Disconnect from the database.
     * if there is a transaction in progress, it will be canceled.
     *
     * @return bool
     */
    public function close(): bool
    {
        if (false === $this->connected()) {
            return true;
        }

        if (self::$engine->inTransaction(self::$link) && !$this->rollback()) {
            $message = new Message();
            $message->error($this->lastErrorMessage());
            return false;
        }

        if (self::$engine->close(self::$link)) {
            self::$link = null;
        }

        return (false === $this->connected());
    }

    /**
     * Record the statements executed in the database.
     *
     * @return bool
     */
    public function commit(): bool
    {
        return self::$engine->commit(self::$link);
    }

    /**
     * Connect to the database.
     *
     * @return bool
     */
    public function connect(): bool
    {
        if ($this->connected()) {
            return true;
        }

        $error = '';
        self::$link = self::$engine->connect($error);
        return $this->connected();
    }

    /**
     * Returns True if it is connected to the database.
     *
     * @return bool
     */
    public function connected(): bool
    {
        return (bool)self::$link;
    }

    /**
     * Escape the quotes from the column name.
     *
     * @param string $name
     * @return string
     */
    public function escapeColumn(string $name): string
    {
        return self::$engine->escapeColumn(self::$link, $name);
    }

    /**
     * Escape the quotes from the text string.
     *
     * @param string $str
     * @return string
     */
    public function escapeString(string $str): string
    {
        return self::$engine->escapeString(self::$link, $str);
    }

    /**
     * Execute SQL statements on the database (inserts, updates or deletes).
     * To make selects, it is better to use select () or selectLimit ().
     * If there is no open transaction, one starts, queries are executed
     * If the transaction has opened it in the call, it closes it confirming
     * or discarding according to whether it has gone well or has given an error
     *
     * @param string $sql
     * @return bool
     */
    public function exec(string $sql): bool
    {
        if (false === $this->connected()) {
            return false;
        }

        $inTransaction = $this->inTransaction();
        $this->beginTransaction();
        $result = self::$engine->exec(self::$link, $sql);
        if (false === $result) {
            $message = new Message();
            $message->error($this->lastErrorMessage());
        }

        if ($inTransaction) {
            return $result;                 // If it was already in a transaction, return result of execution
        }

        if ($result) {
            return $this->commit();
        }

        $this->rollback();
        return false;
    }

    /**
     * Return the database engine used
     *
     * @return MysqlEngine
     */
    public function getEngine(): MysqlEngine
    {
        return self::$engine;
    }

    /**
     * Gets the operator for the database engine
     *
     * @param string $operator
     * @return string
     */
    public function getOperator(string $operator): string
    {
        return self::$engine->getOperator($operator);
    }

    /**
     * Indicates if there is an open transaction.
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return self::$engine->inTransaction(self::$link);
    }

    /**
     * Returns the last run statement error.
     *
     * @return string
     */
    public function lastErrorMessage(): string
    {
        return self::$engine->errorMessage(self::$link);
    }

    /**
     * Returns the last ID assigned when doing an INSERT in the database.
     *
     * @return int|bool
     */
    public function lastval()
    {
        $aux = $this->select(self::$engine->lastValue());
        return empty($aux) ? false : $aux[0]['num'];
    }

    /**
     * Undo the statements executed in the database.
     *
     * @return bool
     */
    public function rollback(): bool
    {
        return self::$engine->rollback(self::$link);
    }

    /**
     * Execute a SQL statement of type select, and return
     * an array with the results, or an empty array in case of failure.
     *
     * @param string $sql
     * @return array
     */
    public function select(string $sql): array
    {
        return $this->selectLimit($sql, 0);
    }

    /**
     * Execute a SQL statement of type select, but with pagination,
     * and return an array with the results or an empty array in case of failure.
     * Limit is the number of items you want to return. Offset is the result
     * number from which you want it to start.
     *
     * @param string $sql
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function selectLimit(string $sql, int $limit = APP_ITEM_LIMIT, int $offset = 0): array
    {
        if (false === $this->connected()) {
            return [];
        }

        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset . ';';
        }

        $result = self::$engine->select(self::$link, $sql);
        return empty($result) ? [] : $result;
    }

    /**
     * Transforms a variable into a valid text string to be used in a SQL query.
     *
     * @param mixed $val
     * @return string
     */
    public function var2str($val): string
    {
        // Null value
        if ($val === null) {
            return 'NULL';
        }

        // Boolean value
        if (is_bool($val)) {
            return $val ? 'TRUE' : 'FALSE';
        }

        // Date value
        if (preg_match("/^([\d]{1,2})-([\d]{1,2})-([\d]{4})$/i", $val)) {
            return "'" . date(self::$engine->dateStyle(), strtotime($val)) . "'";
        }

        // DateTime value
        if (preg_match("/^([\d]{1,2})-([\d]{1,2})-([\d]{4}) ([\d]{1,2}):([\d]{1,2}):([\d]{1,2})$/i", $val)) {
            return "'" . date(self::$engine->dateStyle() . ' H:i:s', strtotime($val)) . "'";
        }

        // String value (or other)
        return "'" . $this->escapeString($val) . "'";
    }

    /**
     * Returns the used database engine and the version.
     *
     * @return string
     */
    public function version(): string
    {
        return $this->connected() ? self::$engine->version(self::$link) : '';
    }
}
