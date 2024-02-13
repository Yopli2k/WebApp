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
namespace WebApp\Core\ExtendedController;

use WebApp\Core\DataBase\DataBaseWhere;
use WebApp\Core\Widget\ColumnItem;
use WebApp\Core\Widget\GroupItem;
use WebApp\Core\Widget\VisualItemLoadEngine;
use Symfony\Component\HttpFoundation\Request;


// use FacturaScripts\Core\Base\ToolBox;
// use FacturaScripts\Core\Model\Base\ModelClass;

/**
 * Base definition for the views used in ExtendedControllers
 *
 * @author Carlos García Gómez           <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class BaseView
{
    protected const DEFAULT_TEMPLATE = 'Master/BaseView.html.twig';

    /**
     * Total count of read rows.
     *
     * @var int
     */
    public int $count = 0;

    /**
     * Cursor with data from the model display
     *
     * @var array
     */
    public array $cursor = [];

    /**
     * @var string
     */
    public string $icon;

    /**
     * Model to use in this view.
     *
     * @var mixed
     */
    public mixed $model;

    /**
     * Stores the new code from the save() procedure, to use in loadData().
     *
     * @var ?string
     */
    public ?string $newCode;

    /**
     * Stores the offset for the cursor
     *
     * @var int
     */
    public int $offset = 0;

    /**
     * @var array
     */
    public array $order = [];

    /**
     * @var GroupItem[]
     */
    protected array $columns = [];

    /**
     * @var array
     */
    protected array $modals = [];

    /**
     * @var string
     */
    private string $name;

    /**
     * Columns configuration
     *
     * @var PageOption
     */
    protected PageOption $pageOption;

    /**
     * @var array
     */
    protected array $rows = [];

    /**
     * @var array
     */
    public array $settings;

    /**
     * @var string
     */
    public string $template;

    /**
     * View title
     *
     * @var string
     */
    public string $title;

    /**
     * Stores the where parameters for the cursor.
     *
     * @var DataBaseWhere[]
     */
    public array $where = [];

    /**
     * Loads view data.
     */
    abstract public function loadData(
        string $code = '',
        array $where = [],
        array $order = [],
        int $offset = 0,
        int $limit = APP_ITEM_LIMIT
    ): void;

    /**
     * Process form data.
     */
    abstract public function processFormData(Request $request, string $case);

    /**
     * Construct and initialize the class
     *
     * @param string $name
     * @param string $title
     * @param string $modelName
     * @param string $icon
     */
    public function __construct(string $name, string $title, string $modelName, string $icon)
    {
        if (class_exists($modelName)) {
            $this->model = new $modelName();
        }

        $this->icon = $icon;
        $this->name = $name;
        $this->newCode = null;
        $this->pageOption = new PageOption();
        $this->settings = [
            'active' => true,
            'btnDelete' => true,
            'btnNew' => true,
            'btnSave' => true,
            'btnUndo' => true,
            'card' => true,
            'checkBoxes' => true,
            'clickable' => true,
        ];
        $this->template = static::DEFAULT_TEMPLATE;
        $this->title = $title;
    }

    /**
     * Gets the modal column by the column name
     *
     * @param string $columnName
     * @return ?ColumnItem
     */
    public function columnModalForName(string $columnName): ?ColumnItem
    {
        return $this->getColumnForName($columnName, $this->modals);
    }

    /**
     * Gets the column by the column name
     *
     * @param string $columnName
     * @return ?ColumnItem
     */
    public function columnForName(string $columnName): ?ColumnItem
    {
        return $this->getColumnForName($columnName, $this->columns);
    }

    /**
     * Gets the column by the given field name
     *
     * @param string $fieldName
     * @return ?ColumnItem
     */
    public function columnForField(string $fieldName): ?ColumnItem
    {
        foreach ($this->columns as $group) {
            foreach ($group->columns as $column) {
                if ($column->widget->fieldname === $fieldName) {
                    return $column;
                }
            }
        }

        return null;
    }

    /**
     * Establishes the column's display or read only state.
     *
     * @param string $columnName
     * @param bool $disabled
     * @param string $readOnly
     */
    public function disableColumn(string $columnName, bool $disabled = true, string $readOnly = ''): void
    {
        $column = $this->columnForName($columnName);
        if ($column) {
            $column->display = $disabled ? 'none' : 'left';
            $column->widget->readonly = empty($readOnly) ? $column->widget->readonly : $readOnly;
        }
    }

    /**
     * Returns the column configuration
     *
     * @return GroupItem[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Returns the modal configuration
     *
     * @return GroupItem[]
     */
    public function getModals(): array
    {
        return $this->modals;
    }

    /**
     * @return array
     */
    public function getPagination(): array
    {
        $pages = [];
        $key1 = $key2 = 0;
        $current = 1;

        /// add all pages
        while ($key2 < $this->count) {
            $pages[$key1] = [
                'active' => ($key2 == $this->offset),
                'num' => $key1 + 1,
                'offset' => $key1 * APP_ITEM_LIMIT,
            ];
            if ($key2 == $this->offset) {
                $current = $key1;
            }
            $key1++;
            $key2 += APP_ITEM_LIMIT;
        }

        /// now descarting pages
        foreach (array_keys($pages) as $key2) {
            $middle = intval($key1 / 2);

            /**
             * We discard everything except the first page, the last one, the middle one,
             * the current one, the 5 previous and 5 following ones.
             */
            if (($key2 > 1 && $key2 < $current - 5 && $key2 != $middle) || ($key2 > $current + 5 && $key2 < $key1 - 1 && $key2 != $middle)) {
                unset($pages[$key2]);
            }
        }

        return count($pages) > 1 ? $pages : [];
    }

    /**
     * If it exists, return the specified row type
     *
     * @param string $key
     * @return mixed
     */
    public function getRow(string $key): mixed
    {
        return $this->rows[$key] ?? null;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getViewName(): string
    {
        return $this->name;
    }

    /**
     * Verifies the structure and loads into the model the given data array
     *
     * @param array $data
     */
    public function loadFromData(array &$data): void
    {
        $fieldKey = $this->model->primaryColumn();
        $fieldValue = $data[$fieldKey];
        if ($fieldValue !== $this->model->primaryColumnValue() && $fieldValue !== '') {
            $this->model->loadFromCode($fieldValue);
        }

        $this->model->loadFromData($data, ['action', 'activetab']);
    }

    /**
     * Load the view display from xml view.
     *
     * @return void
     */
    public function loadPageOptions(): void
    {
        $viewName = explode('-', $this->name)[0];
        VisualItemLoadEngine::installXML($viewName, $this->pageOption);
        VisualItemLoadEngine::loadArray($this->columns, $this->modals, $this->rows, $this->pageOption);
    }

    protected function assets()
    {
        // nothing to do. Override this method to add assets into child views.
    }

    /**
     * Gets the column by the column name from source group
     *
     * @param string $columnName
     * @param array $source
     * @return ?ColumnItem
     */
    protected function getColumnForName(string $columnName, array $source): ?ColumnItem
    {
        foreach ($source as $group) {
            foreach ($group->columns as $key => $column) {
                if ($key === $columnName) {
                    return $column;
                }
            }
        }

        return null;
    }
}
