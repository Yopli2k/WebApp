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

/**
 * Visual configuration of the extended views,
 * each PageOption corresponds to a view or tab.
 *
 * @author Jose Antonio Cuello  <yopli2000@gmail.com>
 */
class PageOption
{

    /**
     * Definition of the columns.
     * It's called columns, but it always contains GroupItem at first level,
     * which contains the columns.
     *
     * @var array
     */
    public array $columns;

    /**
     * Definition of modal forms
     *
     * @var array
     */
    public array $modals;

    /**
     * Name of the page (controller).
     *
     * @var string
     */
    public string $name;

    /**
     * Definition for special treatment of data rows
     *
     * @var array
     */
    public array $rows;

    /**
     * Class constructor. Initialize the properties.
     */
    public function __construct()
    {
        $this->name = '';
        $this->clear();
    }

    /**
     * Clear and initialize the properties
     *
     * @return void
     */
    public function clear(): void
    {
        $this->columns = [];
        $this->modals = [];
        $this->rows = [];
    }
}
