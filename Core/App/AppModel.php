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
namespace WebApp\Core\App;

use WebApp\Core\DataBase\DataBase;
use WebApp\Core\DataBase\DataBaseWhere;

/**
 * Base class for all models used in the application.
 * Models are used to access database records.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author José Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class AppModel
{

    /**
     * Provides access to the database.
     *
     * @var DataBase
     */
    protected static DataBase $dataBase;

    /**
     * Provides access to the message system.
     *
     * @var Message
     */
    protected Message $message;

    /**
     * Reset the values of all model properties.
     */
    abstract public function clear(): void;

    /**
     * Assign the values of the $data array to the model properties.
     *
     * @param array $data
     */
    abstract public function loadFromData(array $data = []): void;

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    abstract public static function primaryColumn(): string;

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    abstract public static function tableName(): string;

    /**
     * Insert the model data in the database.
     *
     * @return bool
     */
    abstract protected function insert(): bool;

    /**
     * Returns the list of fields that cannot be empty.
     *
     * @return string[]
     */
    abstract protected function requiredFields(): array;

    /**
     * Update the model data in the database.
     *
     * @return bool
     */
    abstract protected function update(): bool;

    /**
     * Class constructor.
     * If the $data is not empty, it loads the data into the model.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (false === isset(self::$dataBase)) {
            self::$dataBase = new DataBase();
        }

        $this->message = new Message();
        $this->clear();
        if (false === empty($data)) {
            $this->loadFromData($data);
        }
    }

    /**
     * Returns the number of records in the model that meet the condition.
     *    - $where: array of DataBaseWhere which make up the where clause.
     *
     * @param DataBaseWhere[] $where
     * @return int
     */
    public function count(array $where = []): int
    {
        $sql = 'SELECT COUNT(1) AS total FROM ' . static::tableName() . DataBaseWhere::getSQLWhere($where);
        $data = self::$dataBase->select($sql);
        return empty($data) ? 0 : (int)$data[0]['total'];
    }

    /**
     * Remove the model data from the database.
     *
     * @return bool
     */
    public function delete(): bool
    {
        $where = [new DataBaseWhere(static::primaryColumn(), $this->primaryColumnValue())];
        $sql = 'DELETE FROM ' . static::tableName() . DataBaseWhere::getSQLWhere($where);
        return self::$dataBase->exec($sql);
    }

    /**
     * Returns true if the model data is stored in the database.
     *
     * @return bool
     */
    public function exists(): bool
    {
        $sql = 'SELECT 1 FROM ' . static::tableName()
            . ' WHERE ' . static::primaryColumn()
            . ' = ' . self::$dataBase->var2str($this->primaryColumnValue());

        return !empty($this->primaryColumnValue()) && self::$dataBase->select($sql);
    }

    /**
     * Fill the model with the registry values
     * whose primary column corresponds to the value $cod, or according to the condition
     * where indicated, if value is not reported in $cod.
     * Initializes the values of the class if there is no record that meet the above conditions.
     * Returns True if the record exists and False otherwise.
     *
     * @param mixed $code
     * @param array $where
     * @param array $order
     * @return bool
     */
    public function loadFromCode(mixed $code, array $where = [], array $order = []): bool
    {
        $data = $this->getRecord($code, $where, $order);
        if (empty($data)) {
            $this->clear();
            return false;
        }

        $this->loadFromData($data[0]);
        return true;
    }

    /**
     * Returns the current value of the main column of the model.
     *
     * @return mixed
     */
    public function primaryColumnValue(): mixed
    {
        return $this->{$this->primaryColumn()};
    }

    /**
     * Returns the name of the column that describes the model, such as name, description...
     *
     * @return string
     */
    public function primaryDescriptionColumn(): string
    {
        if (property_exists($this, 'name')) {
            return 'name';
        }
        return static::primaryColumn();
    }

    /**
     * Descriptive identifier for humans of the data record
     *
     * @return string
     */
    public function primaryDescription(): string
    {
        $field = $this->primaryDescriptionColumn();
        return $this->{$field} ?? (string)$this->primaryColumnValue();
    }

    /**
     * Stores the model data in the database.
     *
     * @return bool
     */
    public function save(): bool
    {
        if (false === $this->test()) {
            return false;
        }

        if ($this->exists()) {
            return $this->update();
        }

        if ($this->insert()) {
            $this->{static::primaryColumn()} = self::$dataBase->lastval();
            return true;
        }

        return false;
    }

    /**
     * Returns all records that correspond to the selected filters.
     *   - $where: array of DataBaseWhere which make up the where clause.
     *   - $order: fields to use in the order by clausule. For example ['fieldname' => 'ASC']
     *   - $offset: number of records to skip.
     *   - $limit: maximum number of records to return.
     *
     * @param DataBaseWhere[] $where
     * @param array $order
     * @param int $offset
     * @param int $limit
     * @return static[]
     */
    public function select(array $where = [], array $order = [], int $offset = 0, int $limit = 50): array
    {
        $result = [];
        $sql = 'SELECT * FROM ' . static::tableName() . DataBaseWhere::getSQLWhere($where) . $this->getOrderBy($order);
        foreach (self::$dataBase->selectLimit($sql, $limit, $offset) as $row) {
            $result[] = new static($row);
        }

        return $result;
    }

    /**
     * Returns true if there are no errors in the values of the model properties.
     * It runs inside the save method.
     *
     * @return bool
     */
    public function test(): bool
    {
        $keyField = static::primaryColumn();
        if (empty($this->{$keyField})) {
            $this->{$keyField} = null;
        }

        if (false === $this->checkRequiredFields()) {
            $this->message->warning('Debe introducir todos los campos obligatorios.');
            return false;
        }
        return true;
    }

    /**
     * Returns the url where to see / modify the data.
     *
     * @param string $type
     * @param string $list
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'List'): string
    {
        $value = $this->primaryColumnValue();
        $model = $this->modelClassName();
        return match ($type) {
            'edit' => is_null($value) ? 'Edit' . $model : 'Edit' . $model . '?code=' . rawurlencode($value),
            'list' => $list . $model,
            'new' => 'Edit' . $model,
            default => empty($value) ? $list . $model : 'Edit' . $model . '?code=' . rawurlencode($value),
        };
    }

    /**
     * Returns the name of the class of the model.
     *
     * @return string
     */
    protected function modelClassName(): string
    {
        $result = explode('\\', get_class($this));
        return end($result);
    }

    /**
     * @return bool
     */
    private function checkRequiredFields(): bool
    {
        foreach ($this->requiredFields() as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }
        return true;
    }

    /**
     * Convert an array of filters to order by clausule.
     *
     * @param array $order
     * @return string
     */
    private function getOrderBy(array $order): string
    {
        $result = '';
        $coma = ' ORDER BY ';
        foreach ($order as $key => $value) {
            $result .= $coma . $key . ' ' . $value;
            $coma = ', ';
        }

        return $result;
    }

    /**
     * Read the record whose primary column corresponds to the value $cod
     * or the first that meets the indicated condition.
     *
     * @param mixed $code
     * @param array $where
     * @param array $order
     * @return array
     */
    private function getRecord(mixed $code, array $where = [], array $order = []): array
    {
        if (empty($code) && empty($where)) {
            return [];
        }

        if (empty($where)) {
            $where[] = new DataBaseWhere(static::primaryColumn(), $code);
        }

        $sql = 'SELECT * FROM ' . static::tableName() . DataBaseWhere::getSQLWhere($where) . $this->getOrderBy($order);
        return self::$dataBase->selectLimit($sql, 1);
    }
}
