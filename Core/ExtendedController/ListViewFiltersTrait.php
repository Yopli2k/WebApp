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
use WebApp\Core\ListFilter\AutocompleteFilter;
use WebApp\Core\ListFilter\BaseFilter;
use WebApp\Core\ListFilter\CheckboxFilter;
use WebApp\Core\ListFilter\DateFilter;
use WebApp\Core\ListFilter\NumberFilter;
use WebApp\Core\ListFilter\PeriodFilter;
use WebApp\Core\ListFilter\SelectFilter;
use WebApp\Core\ListFilter\SelectWhereFilter;
use WebApp\Model\PageFilter;
use WebApp\Model\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of ListViewFiltersTrait
 *
 * @author Carlos García Gómez           <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
trait ListViewFiltersTrait
{
    /**
     * Filter configuration preset by the user
     *
     * @var BaseFilter[]
     */
    public array $filters = [];

    /**
     * Predefined filter values selected
     *
     * @var int
     */
    public int $pageFilterKey = 0;

    /**
     * List of predefined filter values
     *
     * @var PageFilter[]
     */
    public array $pageFilters = [];

    /**
     * @var bool
     */
    public bool $showFilters = false;

    abstract public function getViewName(): string;

    /**
     * Add an autocomplete type filter to the ListView.
     *
     * @param string $key
     * @param string $label
     * @param string $field
     * @param string $table
     * @param string $fieldcode
     * @param string $fieldtitle
     * @param array $where
     */
    public function addFilterAutocomplete(string $key, string $label, string $field, string $table, string $fieldcode = '', string $fieldtitle = '', array $where = []): void
    {
        $this->filters[$key] = new AutocompleteFilter($key, $field, $label, $table, $fieldcode, $fieldtitle, $where);
    }

    /**
     * Adds a boolean condition type filter to the ListView.
     *
     * @param string $key
     * @param string $label
     * @param string $field
     * @param string $operation
     * @param mixed $matchValue
     * @param array $default
     */
    public function addFilterCheckbox(string $key, string $label = '', string $field = '', string $operation = '=', mixed $matchValue = true, array $default = []): void
    {
        $this->filters[$key] = new CheckboxFilter($key, $field, $label, $operation, $matchValue, $default);
    }

    /**
     * Adds a date type filter to the ListView.
     *
     * @param string $key
     * @param string $label
     * @param string $field
     * @param string $operation
     */
    public function addFilterDatePicker(string $key, string $label = '', string $field = '', string $operation = '>='): void
    {
        $this->filters[$key] = new DateFilter($key, $field, $label, $operation);
    }

    /**
     * Adds a numeric type filter to the ListView.
     *
     * @param string $key
     * @param string $label
     * @param string $field
     * @param string $operation
     */
    public function addFilterNumber(string $key, string $label = '', string $field = '', string $operation = '>='): void
    {
        $this->filters[$key] = new NumberFilter($key, $field, $label, $operation);
    }

    /**
     * Adds a period type filter to the ListView.
     * (period + start date + end date)
     *
     * @param string $key
     * @param string $label
     * @param string $field
     */
    public function addFilterPeriod(string $key, string $label, string $field): void
    {
        $this->filters[$key] = new PeriodFilter($key, $field, $label);
    }

    /**
     * Add a select type filter to a ListView.
     *
     * @param string $key
     * @param string $label
     * @param string $field
     * @param array $values
     */
    public function addFilterSelect(string $key, string $label, string $field, array $values = []): void
    {
        $this->filters[$key] = new SelectFilter($key, $field, $label, $values);
    }

    /**
     * Add a select where type filter to a ListView.
     *
     * @param string $key
     * @param array $values
     * @param string $label
     *
     * Example of values:
     *   [
     *    ['label' => 'Only active', 'where' => [ new DataBaseWhere('suspended', 'FALSE') ]]
     *    ['label' => 'Only suspended', 'where' => [ new DataBaseWhere('suspended', 'TRUE') ]]
     *    ['label' => 'All records', 'where' => []],
     *   ]
     */
    public function addFilterSelectWhere(string $key, array $values, string $label = ''): void
    {
        $this->filters[$key] = new SelectWhereFilter($key, $values, $label);
    }

    /**
     * Removes a saved user filter.
     *
     * @param string $idfilter
     *
     * @return bool
     */
    public function deletePageFilter(string $idfilter): bool
    {
        $pageFilter = new PageFilter();
        if ($pageFilter->loadFromCode($idfilter) && $pageFilter->delete()) {
            // remove form the list
            unset($this->pageFilters[$idfilter]);

            return true;
        }

        return false;
    }

    /**
     * Save filter values for user/s.
     *
     * @param Request $request
     * @param ?User $user
     *
     * @return int
     */
    public function savePageFilter(Request $request, ?User $user): int
    {
        $pageFilter = new PageFilter();
        // Set values data filter
        foreach ($this->filters as $filter) {
            $name = $filter->name();
            $value = $request->request->get($name);
            if ($value !== null) {
                $pageFilter->filters[$name] = $value;
            }
        }

        // If filters values its empty, don't save filter
        if (empty($pageFilter->filters)) {
            return 0;
        }

        // Set basic data and save filter
        $pageFilter->id = $request->request->get('filter-id');
        $pageFilter->description = $request->request->get('filter-description', '');
        $pageFilter->name = explode('-', $this->getViewName())[0];
        $pageFilter->username = $user?->username;
        if (false === $pageFilter->save()) {
            return 0;
        }
        $this->pageFilters[] = $pageFilter;
        return $pageFilter->id;
    }

    /**
     * @param DataBaseWhere[] $where
     */
    private function loadSavedFilters(array $where): void
    {
        $pageFilter = new PageFilter();
        $orderBy = ['username' => 'ASC', 'description' => 'ASC'];
        foreach ($pageFilter->select($where, $orderBy) as $filter) {
            $this->pageFilters[$filter->id] = $filter;
        }
    }

    private function sortFilters(): void
    {
        uasort($this->filters, function ($filter1, $filter2) {
            if ($filter1->ordernum === $filter2->ordernum) {
                return 0;
            }

            return $filter1->ordernum > $filter2->ordernum ? 1 : -1;
        });
    }
}
