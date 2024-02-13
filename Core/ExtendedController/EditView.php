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
 * View definition for edit data used in ExtendedControllers
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class EditView extends BaseView
{

    const DEFAULT_TEMPLATE = 'Master/EditView.html.twig';

    /**
     * Load the data in the model property, according to the code specified.
     *
     * @param string          $code
     * @param DataBaseWhere[] $where
     * @param array           $order
     * @param int             $offset
     * @param int             $limit
     */
    public function loadData(string $code = '', array $where = [], array $order = [], int $offset = 0, int $limit = APP_ITEM_LIMIT): void
    {
        if ($this->newCode !== null) {
            $code = $this->newCode;
        }

        if (empty($code) && empty($where)) {
            return;
        }

        if ($this->model->loadFromCode($code, $where, $order)) {
            $this->count = 1;
        }
    }

    /**
     *
     * @param Request $request
     * @param string  $case
     */
    public function processFormData(Request $request, string $case): void
    {
        switch ($case) {
            case 'edit':
                foreach ($this->getColumns() as $group) {
                    $group->processFormData($this->model, $request);
                }
                break;

            case 'load':
                $exclude = ['action', 'code', 'option'];
                foreach ($request->query->all() as $key => $value) {
                    if (false === in_array($key, $exclude)) {
                        $this->model->{$key} = $value;
                    }
                }
                break;
        }
    }
}
