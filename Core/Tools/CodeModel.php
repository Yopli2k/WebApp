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
namespace WebApp\Core\Tools;

use WebApp\Core\DataBase\DataBase;
use WebApp\Core\DataBase\DataBaseWhere;

/**
 * Auxiliary model to load a list of codes and their descriptions
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 * @author Carlos García Gómez           <carlos@facturascripts.com>
 */
class CodeModel
{
    private const ALL_LIMIT = 1000;

    /**
     * It provides direct access to the database.
     *
     * @var ?DataBase
     */
    protected static ?DataBase $dataBase = null;

    /**
     * Value of the code field of the model read.
     *
     * @var mixed
     */
    public mixed $code;

    /**
     * Value of the field description of the model read.
     *
     * @var string
     */
    public string $description;

    /**
     * Constructor and class initializer.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (empty($data)) {
            $this->code = '';
            $this->description = '';
        } else {
            $this->code = $data['code'];
            $this->description = $data['description'];
        }
    }

    /**
     * Load a CodeModel list (code and description) for the indicated table.
     *
     * @param string $tableName
     * @param string $fieldCode
     * @param string $fieldDescription
     * @param bool $addEmpty
     * @param array $where
     * @return static[]
     */
    public static function all(
        string $tableName,
        string $fieldCode,
        string $fieldDescription,
        bool $addEmpty = true,
        array $where = [])
    : array
    {
        $result = [];
        if ($addEmpty) {
            $result[] = new static(['code' => null, 'description' => '------']);
        }

        self::initDataBase();
        $sql = 'SELECT DISTINCT ' . $fieldCode . ' AS code, ' . $fieldDescription . ' AS description '
            . 'FROM ' . $tableName . DataBaseWhere::getSQLWhere($where) . ' ORDER BY 2 ASC';
        foreach (self::$dataBase->selectLimit($sql, 15) as $row) {
            $result[] = new static($row);
        }
        return $result;
    }

    /**
     * Convert an associative array (code and value) into a CodeModel array.
     *
     * @param array $data
     * @param bool $addEmpty
     * @return static[]
     */
    public static function array2codeModel(array $data, bool $addEmpty = true): array
    {
        $result = [];
        if ($addEmpty) {
            $result[] = new static(['code' => null, 'description' => '------']);
        }

        foreach ($data as $key => $value) {
            $row = ['code' => $key, 'description' => $value];
            $result[] = new static($row);
        }

        return $result;
    }

    /**
     * Returns a codemodel with the selected data.
     *
     * @param string $tableName
     * @param string $fieldCode
     * @param ?string $code
     * @param string $fieldDescription
     *
     * @return static
     */
    public function get(string $tableName, string $fieldCode, ?string $code, string $fieldDescription): static
    {
        if (empty($code)) {
            return new static();
        }

        self::initDataBase();
        $sql = 'SELECT ' . $fieldCode . ' AS code, ' . $fieldDescription . ' AS description FROM '
            . $tableName . ' WHERE ' . $fieldCode . ' = ' . self::$dataBase->var2str($code);
        $data = self::$dataBase->selectLimit($sql, 1);
        return empty($data) ? new static() : new static($data[0]);
    }

    /**
     * Returns a description with the selected data.
     *
     * @param string $tableName
     * @param string $fieldCode
     * @param ?string $code
     * @param string $fieldDescription
     * @return ?string
     */
    public function getDescription(string $tableName, string $fieldCode, ?string $code, string $fieldDescription): ?string
    {
        $model = $this->get($tableName, $fieldCode, $code, $fieldDescription);
        if (false === empty($model->description)) {
            return $model->description;
        }

        return empty($code) ? '' : $code;
    }

    /**
     * Load a CodeModel list (code and description) for the indicated table and search.
     *
     * @param string $tableName
     * @param string $fieldCode
     * @param string $fieldDescription
     * @param string $query
     * @param DataBaseWhere[] $where
     * @return static[]
     */
    public static function search(string $tableName, string $fieldCode, string $fieldDescription, string $query, array $where = []): array
    {
        $fields = $fieldCode . '|' . $fieldDescription;
        $where[] = new DataBaseWhere($fields, mb_strtolower($query, 'UTF8'), 'LIKE');
        return self::all($tableName, $fieldCode, $fieldDescription, false, $where);
    }

    /**
     * Inits database connection.
     */
    protected static function initDataBase(): void
    {
        if (self::$dataBase === null) {
            self::$dataBase = new DataBase();
        }
    }
}
