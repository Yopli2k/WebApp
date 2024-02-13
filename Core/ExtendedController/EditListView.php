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
use Symfony\Component\HttpFoundation\Request;

/**
 * View definition for its use in ExtendedControllers
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class EditListView extends BaseView
{

    const DEFAULT_TEMPLATE = 'Master/EditListViewInLine.html.twig';

    /**
     * Indicates if the view has been selected by the user.
     *
     * @var bool
     */
    public ?bool $selected;

    /**
     * Load the data in the cursor property, according to the where filter specified.
     * Adds an empty row/model at the end of the loaded data.
     *
     * @param string $code
     * @param DataBaseWhere[] $where
     * @param array $order
     * @param int $offset
     * @param int $limit
     */
    public function loadData(string $code = '', array $where = [], array $order = [], int $offset = -1, int $limit = APP_ITEM_LIMIT): void
    {
        $this->offset = $offset < 0 ? $this->offset : $offset;
        $this->order = empty($order) ? $this->order : $order;

        $finalWhere = empty($where) ? $this->where : $where;
        $this->count = is_null($this->model) ? 0 : $this->model->count($finalWhere);

        if ($this->count > 0) {
            $this->cursor = $this->model->select($finalWhere, $this->order, $this->offset, $limit);
        }

        $this->where = $finalWhere;
        if ($this->model !== null) {
            foreach (DataBaseWhere::getFieldsFilter($this->where) as $field => $value) {
                $this->model->{$field} = $value;
            }
        }
    }

    /**
     * Process form data needed.
     *
     * @param Request $request
     * @param string $case
     */
    public function processFormData(Request $request, string $case): void
    {
        switch ($case) {
            case 'edit':
                foreach ($this->getColumns() as $group) {
                    $group->processFormData($this->model, $request);
                }
                $this->selected = $request->request->get('code');
                break;

            case 'load':
                $this->offset = (int)$request->request->get('offset', 0);
                break;
        }
    }
}
