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
use WebApp\Core\Tools\Tools;
use WebApp\Core\Widget\ColumnItem;
use WebApp\Core\Widget\RowStatus;
use Symfony\Component\HttpFoundation\Request;

/**
 * View definition for its use in ListController
 *
 * @author Carlos García Gómez           <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ListView extends BaseView
{
    use ListViewFiltersTrait;

    protected const DEFAULT_TEMPLATE = 'Master/ListView.html.twig';

    /** @var string */
    public string $orderKey = '';

    /** @var array */
    public array $orderOptions = [];

    /** @var string */
    public string $query = '';

    /** @var array */
    public array $searchFields = [];

    public function addColor(string $fieldName, $value, string $color, string $title = ''): void
    {
        if (false === isset($this->rows['status'])) {
            $this->rows['status'] = new RowStatus([]);
        }

        $this->rows['status']->options[] = [
            'tag' => 'option',
            'children' => [],
            'color' => $color,
            'fieldname' => $fieldName,
            'text' => $value,
            'title' => $title
        ];
    }

    /**
     * Adds a field to the Order By list.
     * Default values: 0 = None, 1 = ASC, 2 = DESC
     * If the default value is 0, the first option will be selected.
     *
     * @param array $fields
     * @param string $label
     * @param int $default
     */
    public function addOrderBy(array $fields, string $label, int $default = 0): void
    {
        $key1 = count($this->orderOptions);
        $this->orderOptions[$key1] = [
            'fields' => $fields,
            'label' => $label,
            'type' => 'ASC'
        ];

        $key2 = count($this->orderOptions);
        $this->orderOptions[$key2] = [
            'fields' => $fields,
            'label' => $label,
            'type' => 'DESC'
        ];

        if ($default === 2) {
            $this->setSelectedOrderBy($key2);
        } elseif ($default === 1 || empty($this->order)) {
            $this->setSelectedOrderBy($key1);
        }
    }

    /**
     * Adds a list of fields to the search in the ListView.
     * To use integer columns, use CAST(columnName AS CHAR(50)).
     *
     * @param array $fields
     */
    public function addSearchFields(array $fields): void
    {
        foreach ($fields as $field) {
            $this->searchFields[] = $field;
        }
    }

    public function btnNewUrl(): string
    {
        $url = empty($this->model) ? '' : $this->model->url('new');
        $params = [];
        foreach (DataBaseWhere::getFieldsFilter($this->where) as $key => $value) {
            if ($value !== false) {
                $params[] = $key . '=' . $value;
            }
        }

        return empty($params) ? $url : $url . '?' . implode('&', $params);
    }

    /**
     * @return ColumnItem[]
     */
    public function getColumns(): array
    {
        foreach ($this->columns as $group) {
            return $group->columns;
        }

        return [];
    }

    /**
     * Loads the data in the cursor property, according to the where filter specified.
     *
     * @param string $code
     * @param DataBaseWhere[] $where
     * @param array $order
     * @param int $offset
     * @param int $limit
     */
    public function loadData(
        string $code = '',
        array $where = [],
        array $order = [],
        int $offset = -1,
        int $limit = APP_ITEM_LIMIT
    ): void
    {
        $this->offset = $offset < 0 ? $this->offset : $offset;
        $this->order = empty($order) ? $this->order : $order;
        $this->where = array_merge($where, $this->where);
        $this->count = is_null($this->model) ? 0 : $this->model->count($this->where);

        // avoid overflow
        if ($this->offset > $this->count) {
            $this->offset = 0;
        }

        $this->cursor = [];
        if ($this->count > 0) {
            $this->cursor = $this->model->select($this->where, $this->order, $this->offset, $limit);
        }
    }

    /**
     * Load the view display from xml view.
     * Add saved filters.
     *
     * @return void
     */
    public function loadPageOptions(): void
    {
        parent::loadPageOptions();
        $this->loadSavedFilters([]);
    }

    /**
     * Process form data needed.
     *
     * @param Request $request
     * @param string $case
     */
    public function processFormData(Request $request,string $case): void
    {
        switch ($case) {
            case 'edit':
                $name = $this->settings['modalInsert'] ?? '';
                if (empty($name)) {
                    break;
                }
                $modals = $this->getModals();
                foreach ($modals[$name]->columns as $group) {
                    $group->processFormData($this->model, $request);
                }
                break;

            case 'load':
                $this->sortFilters();
                $this->processFormDataLoad($request);
                break;

            case 'preload':
                $this->sortFilters();
                foreach ($this->filters as $filter) {
                    $filter->getDataBaseWhere($this->where);
                }
                break;
        }
    }

    /**
     * Adds assets to the asset manager.
     */
    protected function assets()
    {
        // TODO: add custom assets to view template
        // AssetManager::add('js', APP_ROUTE . '/Assets/JS/ListView.js?v=2');
    }

    private function processFormDataLoad(Request $request): void
    {
        $this->offset = (int)$request->request->get('offset', 0);
        $this->setSelectedOrderBy($request->request->get('order', ''));

        // query
        $this->query = $request->request->get('query', '');
        if ('' !== $this->query) {
            $fields = implode('|', $this->searchFields);
            $this->where[] = new DataBaseWhere($fields, Tools::noHtml($this->query), 'XLIKE');
        }

        // filtro guardado seleccionado?
        $this->pageFilterKey = $request->request->get('loadfilter', 0);
        if ($this->pageFilterKey) {
            $filterLoad = [];
            // cargamos los valores en la request
            foreach ($this->pageFilters as $item) {
                if ($item->id == $this->pageFilterKey) {
                    $request->request->add($item->filters);
                    $filterLoad = $item->filters;
                    break;
                }
            }
            // aplicamos los valores de la request a los filtros
            foreach ($this->filters as $filter) {
                $key = 'filter' . $filter->key;
                $filter->readonly = true;
                if (array_key_exists($key, $filterLoad)) {
                    $filter->setValueFromRequest($request);
                    if ($filter->getDataBaseWhere($this->where)) {
                        $this->showFilters = true;
                    }
                }
            }
            return;
        }

        // filters
        foreach ($this->filters as $filter) {
            $filter->setValueFromRequest($request);
            if ($filter->getDataBaseWhere($this->where)) {
                $this->showFilters = true;
            }
        }
    }

    /**
     * Checks and establishes the selected value in the Order By
     *
     * @param string $orderKey
     */
    protected function setSelectedOrderBy(string $orderKey): void
    {
        if (isset($this->orderOptions[$orderKey])) {
            $this->order = [];
            $option = $this->orderOptions[$orderKey];
            foreach ($option['fields'] as $field) {
                $this->order[$field] = $option['type'];
            }

            $this->orderKey = $orderKey;
        }
    }
}
