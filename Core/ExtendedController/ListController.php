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
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller that lists the data in table mode
 *
 * @author Carlos García Gómez           <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class ListController extends BaseController
{
    /**
     * Initializes all the objects and properties.
     *
     * @param string $className
     * @param string $uri
     */
    public function __construct(string $className, string $uri = '')
    {
        parent::__construct($className, $uri);
        $this->setTemplate('Master/ListController');
    }

    /**
     * Runs the controller's logic.
     * if return false, the controller break the execution.
     *
     * @param Response $response
     * @return bool
     */
    public function exec(Response &$response): bool
    {
        if (false === parent::exec($response)) {
            return false;
        }

        // Get action and execute if not empty
        $action = $this->request->request->get('action', $this->request->query->get('action', ''));
        if (false === $this->execPreviousAction($action)) {
            return false;
        }

        // Load data for every view
        foreach ($this->views as $viewName => $view) {
            $case = $this->active == $viewName ? 'load' : 'preload';
            $view->processFormData($this->request, $case);
            $this->loadData($viewName, $view);
        }

        // Execute actions after loading data
        $this->execAfterAction($action);
        return true;
    }

    /**
     * Adds a new color option to the list.
     *
     * @param string $viewName
     * @param string $fieldName
     * @param mixed $value
     * @param string $color
     * @param string $title
     */
    protected function addColor(string $viewName, string $fieldName, mixed $value, string $color, string $title = ''): void
    {
        $this->views[$viewName]->addColor($fieldName, $value, $color, $title);
    }

    /**
     * Add an autocomplete type filter to the ListView.
     *
     * @param string $viewName
     * @param string $key (Filter identifier)
     * @param string $label (Human reader description)
     * @param string $field (Field of the model to apply filter)
     * @param string $table (Table to search)
     * @param string $fieldcode (Primary column of the table to search and match)
     * @param string $fieldtitle (Column to show name or description)
     * @param array $where (Extra where conditions)
     */
    protected function addFilterAutocomplete(string $viewName, string $key, string $label, string $field, string $table, string $fieldcode = '', string $fieldtitle = '', array $where = []): void
    {
        $this->views[$viewName]->addFilterAutocomplete($key, $label, $field, $table, $fieldcode, $fieldtitle, $where);
    }

    /**
     * Adds a boolean condition type filter to the ListView.
     *
     * @param string $viewName
     * @param string $key (Filter identifier)
     * @param string $label (Human reader description)
     * @param string $field (Field of the model to apply filter)
     * @param string $operation (operation to perform with match value)
     * @param mixed $matchValue (Value to match)
     * @param DataBaseWhere[] $default (where to apply when filter is empty)
     */
    protected function addFilterCheckbox(string $viewName, string $key, string $label = '', string $field = '', string $operation = '=', mixed $matchValue = true, array $default = []): void
    {
        $this->views[$viewName]->addFilterCheckbox($key, $label, $field, $operation, $matchValue, $default);
    }

    /**
     * Adds a date type filter to the ListView.
     *
     * @param string $viewName
     * @param string $key (Filter identifier)
     * @param string $label (Human reader description)
     * @param string $field (Field of the table to apply filter)
     * @param string $operation (Operation to perform)
     */
    protected function addFilterDatePicker(string $viewName, string $key, string $label = '', string $field = '', string $operation = '>='): void
    {
        $this->views[$viewName]->addFilterDatePicker($key, $label, $field, $operation);
    }

    /**
     * Adds a numeric type filter to the ListView.
     *
     * @param string $viewName
     * @param string $key (Filter identifier)
     * @param string $label (Human reader description)
     * @param string $field (Field of the table to apply filter)
     * @param string $operation (Operation to perform)
     */
    protected function addFilterNumber(string $viewName, string $key, string $label = '', string $field = '', string $operation = '>='): void
    {
        $this->views[$viewName]->addFilterNumber($key, $label, $field, $operation);
    }

    /**
     * Adds a period type filter to the ListView.
     * (period + start date + end date)
     *
     * @param string $viewName
     * @param string $key (Filter identifier)
     * @param string $label (Human reader description)
     * @param string $field (Field of the table to apply filter)
     */
    protected function addFilterPeriod(string $viewName, string $key, string $label, string $field): void
    {
        $this->views[$viewName]->addFilterPeriod($key, $label, $field);
    }

    /**
     * Add a select type filter to a ListView.
     *
     * @param string $viewName
     * @param string $key (Filter identifier)
     * @param string $label (Human reader description)
     * @param string $field (Field of the table to apply filter)
     * @param array $values (Values to show)
     */
    protected function addFilterSelect(string $viewName, string $key, string $label, string $field, array $values = []): void
    {
        $this->views[$viewName]->addFilterSelect($key, $label, $field, $values);
    }

    /**
     * Add a select where type filter to a ListView.
     *
     * @param string $viewName
     * @param string $key (Filter identifier)
     * @param array $values (Values to show)
     * @param string $label (Human reader description)
     *
     * Example of values:
     *   [
     *    ['label' => 'Only active', 'where' => [ new DataBaseWhere('suspended', 'FALSE') ]]
     *    ['label' => 'Only suspended', 'where' => [ new DataBaseWhere('suspended', 'TRUE') ]]
     *    ['label' => 'All records', 'where' => []],
     *   ]
     */
    protected function addFilterSelectWhere(string $viewName, string $key, array $values, string $label = ''): void
    {
        $this->views[$viewName]->addFilterSelectWhere($key, $values, $label);
    }

    /**
     * Adds an order field to the ListView.
     *
     * @param string $viewName
     * @param array $fields
     * @param string $label
     * @param int $default (0 = None, 1 = ASC, 2 = DESC)
     */
    protected function addOrderBy(string $viewName, array $fields, string $label = '', int $default = 0): void
    {
        $orderLabel = empty($label) ? $fields[0] : $label;
        $this->views[$viewName]->addOrderBy($fields, $orderLabel, $default);
    }

    /**
     * Adds a list of fields to the search in the ListView.
     * To use integer columns, use CAST(columnName AS CHAR(50)).
     *
     * @param string $viewName
     * @param array $fields
     */
    protected function addSearchFields(string $viewName, array $fields): void
    {
        $this->views[$viewName]->addSearchFields($fields);
    }

    /**
     * Creates and adds a ListView to the controller.
     *
     * @param string $viewName
     * @param string $modelName
     * @param string $viewTitle
     * @param string $icon
     */
    protected function addView(string $viewName, string $modelName, string $viewTitle = '', string $icon = 'fas fa-search'): void
    {
        $title = empty($viewTitle) ? $this->title : $viewTitle;
        $view = new ListView($viewName, $title, self::MODEL_NAMESPACE . $modelName, $icon);
        $this->addCustomView($viewName, $view);
        $this->setSettings($viewName, 'card', false);
    }

    /**
     * Runs the controller actions after data read.
     *
     * @param string $action
     */
    protected function execAfterAction(string $action): void
    {
        if ($action == 'delete-ok') {
            $this->message->info('Registro eliminado correctamente.');
        }
    }

    /**
     * Runs the actions that alter the data before reading it.
     *
     * @param string $action
     * @return bool
     */
    protected function execPreviousAction(string $action): bool
    {
        switch ($action) {
            case 'autocomplete':
                $this->setTemplate(false);
                $results = $this->autocompleteAction();
                $this->response->setContent(json_encode($results));
                return false;

            case 'delete':
                $this->deleteAction();
                break;
        }

        return true;
    }

    /**
     * @param string $viewName
     * @param mixed $view
     */
    protected function loadData(string $viewName, mixed $view): void
    {
        $view->loadData('');
    }
}
