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
namespace WebApp\Controller;

use WebApp\Core\DataBase\DataBaseWhere;
use WebApp\Core\ExtendedController\ListController;

class ListUser extends ListController
{

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['title'] = 'Usuarios';
        $data['icon'] = 'fas fa-users';
        return $data;
    }

    protected function createViews(): void
    {
        $this->createViewsUsers();
    }

    /**
     * @param string $viewName
     */
    private function createViewsUsers(string $viewName = 'ListUser'): void
    {
        $this->addView($viewName, 'User', 'Usuarios', 'fas fa-users');
        $this->addSearchFields($viewName, ['name', 'username', 'email']);
        $this->addOrderBy($viewName, ['name'], 'Nombre');
        $this->addOrderBy($viewName, ['username'], 'Usuario');
        $this->addOrderBy($viewName, ['email'], 'Correo');
        $this->setSettings($viewName, 'btnDelete', false);
        $this->setSettings($viewName, 'checkBoxes', false);
    }
}