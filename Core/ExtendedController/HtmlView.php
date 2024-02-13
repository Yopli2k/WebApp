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

use WebApp\Core\Widget\VisualItemLoadEngine;
use Symfony\Component\HttpFoundation\Request;

/**
 * View definition for free html used in ExtendedControllers
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class HtmlView extends BaseView
{

    /**
     * HtmlView constructor and initialization.
     *
     * @param string $name
     * @param string $title
     * @param string $modelName
     * @param string $fileName
     * @param string $icon
     */
    public function __construct(string $name, string $title, string $modelName, string $fileName, string $icon)
    {
        parent::__construct($name, $title, $modelName, $icon);
        $this->template = $fileName . '.html.twig';
    }

    /**
     * @param string $code
     * @param array  $where
     * @param array  $order
     * @param int    $offset
     * @param int    $limit
     */
    public function loadData(string $code = '', array $where = [], array $order = [], int $offset = 0, int $limit = APP_ITEM_LIMIT): void
    {
        if (empty($code) && empty($where)) {
            return;
        }

        $this->model->loadFromCode($code, $where, $order);
        if (false === empty($where)) {
            $this->count = $this->model->count($where);
            $this->cursor = $this->model->select($where, $order, $offset, $limit);
        }
    }

    /**
     * Load the view display from xml view.
     *
     * @return void
     */
    public function loadPageOptions(): void
    {
        VisualItemLoadEngine::loadArray($this->columns, $this->modals, $this->rows, $this->pageOption);
    }

    /**
     *
     * @param Request $request
     * @param string  $case
     */
    public function processFormData(Request $request, string $case): void
    {
    }
}
