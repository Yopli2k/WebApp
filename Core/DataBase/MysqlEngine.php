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

use Exception;
use mysqli;

/**
 * Class to connect with MySQL.
 *
 * @author Carlos García Gómez           <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class MysqlEngine
{
    /**
     * Last error message.
     *
     * @var string
     */
    private $lastErrorMsg = '';

    /**
     * Open transaction list.
     *
     * @var array
     */
    private $transactions = [];

    /**
     * Constructor and class initialization.
     */
    public function __construct()
    {
    }

    /**
     * Destructor class.
     */
    public function __destruct()
    {
        $this->rollbackTransactions();
    }

    /**
     * Starts an SQL transaction.
     *
     * @param mysqli $link
     * @return bool
     */
    public function beginTransaction(mysqli $link): bool
    {
        $result = $this->exec($link, 'START TRANSACTION;');
        if ($result) {
            $this->transactions[] = $link;
        }

        return $result;
    }

    /**
     * Disconnect from the database.
     *
     * @param mysqli $link
     * @return bool
     */
    public function close(mysqli $link): bool
    {
        $this->rollbackTransactions();
        return $link->close();
    }

    /**
     * Commits changes in a SQL transaction.
     *
     * @param mysqli $link
     * @return bool
     */
    public function commit(mysqli $link): bool
    {
        $result = $this->exec($link, 'COMMIT;');
        if ($result && in_array($link, $this->transactions)) {
            $this->unsetTransaction($link);
        }

        return $result;
    }

    /**
     * Connects to the database.
     *
     * @param string $error
     * @return null|mysqli
     */
    public function connect(string &$error): ?mysqli
    {
        if (false === class_exists('mysqli')) {
            $error = 'php-mysql-not-found';
            return null;
        }

        $result = new mysqli(APP_DB_HOST, APP_DB_USER, APP_DB_PASS, APP_DB_NAME, (int)APP_DB_PORT);
        if ($result->connect_errno) {
            $error = $result->connect_error;
            $this->lastErrorMsg = $error;
            return null;
        }

        $charset = defined('APP_DB_CHARSET') ? APP_DB_CHARSET : 'utf8mb4';
        $result->set_charset($charset);
        $result->autocommit(false);
        return $result;
    }

    /**
     * Returns the date format from the database engine
     *
     * @return string
     */
    public function dateStyle(): string
    {
        return 'Y-m-d';
    }

    /**
     * Returns the last run statement error.
     *
     * @param mysqli $link
     * @return string
     */
    public function errorMessage(mysqli $link): string
    {
        return empty($link->error) ? $this->lastErrorMsg : $link->error;
    }

    /**
     * Escapes the column name.
     *
     * @param mysqli $link
     * @param string $name
     * @return string
     */
    public function escapeColumn(mysqli $link, string $name): string
    {
        return '`' . $name . '`';
    }

    /**
     * Escapes quotes from a text string.
     *
     * @param mysqli $link
     * @param string $str
     * @return string
     */
    public function escapeString(mysqli $link, string $str): string
    {
        return $link->escape_string($str);
    }

    /**
     * Runs SQL statement in the database (inserts, updates or deletes).
     *
     * @param mysqli $link
     * @param string $sql
     * @return bool
     */
    public function exec(mysqli $link, string $sql): bool
    {
        try {
            if ($link->multi_query($sql)) {
                do {
                    $more = $link->more_results() && $link->next_result();
                } while ($more);
            }
            return $link->errno === 0;
        } catch (Exception $err) {
            $this->lastErrorMsg = $err->getMessage();
        }
        return false;
    }

    /**
     * Indicates the operator for the database.
     * Allow to change the operator for specials sqls.
     *
     * @param string $operator
     * @return string
     */
    public function getOperator(string $operator): string
    {
        return $operator;
    }

    /**
     * Indicates if the connection has an active transaction.
     *
     * @param mysqli $link
     * @return bool
     */
    public function inTransaction(mysqli $link): bool
    {
        return in_array($link, $this->transactions);
    }

    /**
     * Returns the SQL to get last ID assigned when performing an INSERT in the database
     *
     * @return string
     */
    public function lastValue(): string
    {
        return 'SELECT LAST_INSERT_ID() as num;';
    }

    /**
     * Rolls back a transaction.
     *
     * @param mysqli $link
     * @return bool
     */
    public function rollback(mysqli $link): bool
    {
        $result = $this->exec($link, 'ROLLBACK;');
        if (in_array($link, $this->transactions)) {
            $this->unsetTransaction($link);
        }
        return $result;
    }

    /**
     * Runs a SELECT SQL statement, and returns an array with the results,
     * or an empty array when it fails.
     *
     * @param mysqli $link
     * @param string $sql
     * @return array
     */
    public function select(mysqli $link, string $sql): array
    {
        $result = [];
        try {
            $aux = $link->query($sql);
            if ($aux) {
                while ($row = $aux->fetch_array(MYSQLI_ASSOC)) {
                    $result[] = $row;
                }
                $aux->free();
            }
        } catch (Exception $err) {
            $this->lastErrorMsg = $err->getMessage();
            $result = [];
        }
        return $result;
    }

    /**
     * Returns the database engine and its version.
     *
     * @param mysqli $link
     * @return string
     */
    public function version(mysqli $link): string
    {
        return 'MYSQL ' . $link->server_version;
    }

    /**
     * Rollback all active transactions.
     */
    private function rollbackTransactions()
    {
        foreach ($this->transactions as $link) {
            $this->rollback($link);
        }
    }

    /**
     * Delete from the list the specified transaction.
     *
     * @param mysqli $link
     */
    private function unsetTransaction(mysqli $link)
    {
        $count = 0;
        foreach ($this->transactions as $trans) {
            if ($trans === $link) {
                array_splice($this->transactions, $count, 1);
                break;
            }
            ++$count;
        }
    }
}
